<?php
require_once '../config/init.php';

// Faculty and Department data
$faculties = [
    'Faculty of Arts and Humanities' => [
        'Department of Bangla',
        'Department of English',
        'Department of History',
        'Department of Philosophy'
    ],
    'Faculty of Mathematical and Physical Sciences' => [
        'Department of Chemistry',
        'Department of Computer Science and Engineering',
        'Department of Environmental Sciences',
        'Department of Geological Sciences',
        'Department of Mathematics',
        'Department of Physics',
        'Department of Statistics and Data Science'
    ],
    'Faculty of Biological Sciences' => [
        'Department of Botany',
        'Department of Biochemistry and Molecular Biology',
        'Department of Zoology',
        'Department of Pharmacy'
    ]
];

// Check if Google user data exists
if (!isset($_SESSION['google_user'])) {
    header('Location: register.php');
    exit();
}

$google_user = $_SESSION['google_user'];
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $faculty = sanitizeInput($_POST['faculty']);
    $department = sanitizeInput($_POST['department']);
    $batch = sanitizeInput($_POST['batch']);
    $mobile_number = sanitizeInput($_POST['mobile_number'] ?? '');
    
    // Validation
    if (empty($faculty) || empty($department) || empty($batch)) {
        $error = 'Please fill in all required fields.';
    } else {
        $conn = getDBConnection();
        
        // Insert new user
        $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, faculty, department, batch, mobile_number, google_id, oauth_provider, email_verified_by_oauth, is_verified, is_active) VALUES (?, ?, NULL, ?, ?, ?, ?, ?, 'google', TRUE, TRUE, TRUE)");
        $stmt->bind_param("sssssss", 
            $google_user['name'],
            $google_user['email'],
            $faculty,
            $department,
            $batch,
            $mobile_number,
            $google_user['google_id']
        );
        
        if ($stmt->execute()) {
            $user_id = $conn->insert_id;
            
            // Download and save profile picture if available
            if (!empty($google_user['picture'])) {
                $profile_dir = UPLOAD_DIR . 'profile_pictures/';
                if (!file_exists($profile_dir)) {
                    mkdir($profile_dir, 0777, true);
                }
                
                $image_data = @file_get_contents($google_user['picture']);
                if ($image_data) {
                    $filename = 'profile_' . $user_id . '_' . time() . '.jpg';
                    file_put_contents($profile_dir . $filename, $image_data);
                    
                    $update_stmt = $conn->prepare("UPDATE users SET profile_picture = ? WHERE user_id = ?");
                    $update_stmt->bind_param("si", $filename, $user_id);
                    $update_stmt->execute();
                    $update_stmt->close();
                }
            }
            
            // Set session
            $_SESSION['user_id'] = $user_id;
            $_SESSION['user_name'] = $google_user['name'];
            $_SESSION['user_email'] = $google_user['email'];
            
            // Clear Google user session
            unset($_SESSION['google_user']);
            
            $stmt->close();
            closeDBConnection($conn);
            
            header('Location: ../dashboard/user-dashboard.php');
            exit();
        } else {
            $error = 'Registration failed. This email may already be registered.';
        }
        
        $stmt->close();
        closeDBConnection($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Registration - JU Campus Notes Hub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-emerald-100 to-purple-100 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-2xl">
        <div class="text-center mb-8">
            <i class="fas fa-graduation-cap text-6xl text-emerald-600 mb-4"></i>
            <h1 class="text-3xl font-bold text-gray-800">Complete Your Registration</h1>
            <p class="text-gray-600 mt-2">Verified with Google: <strong><?php echo htmlspecialchars($google_user['email']); ?></strong></p>
            <p class="text-sm text-green-600 mt-1"><i class="fas fa-check-circle"></i> Email Verified</p>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" class="space-y-6">
            <!-- Faculty -->
            <div>
                <label class="block text-gray-700 font-semibold mb-2">Faculty *</label>
                <select name="faculty" id="faculty" required 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="">Select Faculty</option>
                    <?php foreach ($faculties as $faculty => $departments): ?>
                        <option value="<?php echo $faculty; ?>" <?php echo (isset($_POST['faculty']) && $_POST['faculty'] === $faculty) ? 'selected' : ''; ?>>
                            <?php echo $faculty; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Department -->
            <div>
                <label class="block text-gray-700 font-semibold mb-2">Department *</label>
                <select name="department" id="department" required 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="">Select Department</option>
                </select>
            </div>

            <!-- Batch -->
            <div>
                <label class="block text-gray-700 font-semibold mb-2">Batch *</label>
                <select name="batch" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="">Select Batch</option>
                    <?php for ($i = 54; $i >= 1; $i--): ?>
                        <option value="<?php echo $i; ?>" <?php echo (isset($_POST['batch']) && $_POST['batch'] == $i) ? 'selected' : ''; ?>>
                            Batch <?php echo $i; ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>

            <!-- Mobile Number (Optional) -->
            <div>
                <label class="block text-gray-700 font-semibold mb-2">Mobile Number (Optional)</label>
                <input type="tel" name="mobile_number" 
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"
                       placeholder="01XXXXXXXXX"
                       value="<?php echo isset($_POST['mobile_number']) ? htmlspecialchars($_POST['mobile_number']) : ''; ?>">
            </div>

            <!-- Submit Button -->
            <button type="submit" 
                    class="w-full bg-emerald-600 text-white font-bold py-4 rounded-lg hover:bg-emerald-700 transition duration-200">
                <i class="fas fa-check"></i> Complete Registration
            </button>
        </form>
    </div>

    <script>
        // Dynamic department loading
        const facultyDepartments = <?php echo json_encode($faculties); ?>;
        
        document.getElementById('faculty').addEventListener('change', function() {
            const faculty = this.value;
            const departmentSelect = document.getElementById('department');
            
            departmentSelect.innerHTML = '<option value="">Select Department</option>';
            
            if (faculty && facultyDepartments[faculty]) {
                facultyDepartments[faculty].forEach(function(dept) {
                    const option = document.createElement('option');
                    option.value = dept;
                    option.textContent = dept;
                    departmentSelect.appendChild(option);
                });
            }
        });

        // Trigger change if faculty is already selected
        <?php if (isset($_POST['faculty']) && isset($_POST['department'])): ?>
        document.getElementById('faculty').dispatchEvent(new Event('change'));
        document.getElementById('department').value = '<?php echo $_POST['department']; ?>';
        <?php endif; ?>
    </script>
</body>
</html>
