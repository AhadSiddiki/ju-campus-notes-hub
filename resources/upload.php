<?php
require_once '../config/init.php';
requireLogin();

$conn = getDBConnection();
$user_id = $_SESSION['user_id'];

// Get user information
$stmt = $conn->prepare("SELECT full_name FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

$error = '';
$success = '';

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitizeInput($_POST['title']);
    $description = sanitizeInput($_POST['description']);
    $faculty = sanitizeInput($_POST['faculty']);
    $department = sanitizeInput($_POST['department']);
    $course_code = sanitizeInput($_POST['course_code']);
    $resource_type = sanitizeInput($_POST['resource_type']);
    
    // Validation
    if (empty($title) || empty($faculty) || empty($department) || empty($course_code) || empty($resource_type)) {
        $error = 'Please fill in all required fields.';
    } elseif (!isset($_FILES['file']) || $_FILES['file']['error'] === UPLOAD_ERR_NO_FILE) {
        $error = 'Please select a file to upload.';
    } elseif ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        $error = 'File upload error. Please try again.';
    } elseif ($_FILES['file']['size'] > MAX_FILE_SIZE) {
        $error = 'File size exceeds the maximum limit of ' . formatFileSize(MAX_FILE_SIZE);
    } else {
        $file = $_FILES['file'];
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($file_ext, ALLOWED_EXTENSIONS)) {
            $error = 'Invalid file type. Allowed types: ' . implode(', ', ALLOWED_EXTENSIONS);
        } else {
            // Create upload directory if it doesn't exist
            if (!file_exists(UPLOAD_DIR)) {
                mkdir(UPLOAD_DIR, 0777, true);
            }
            
            // Generate unique filename
            $new_filename = uniqid() . '_' . time() . '.' . $file_ext;
            $upload_path = UPLOAD_DIR . $new_filename;
            
            if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                // Find or create category
                $stmt = $conn->prepare("SELECT category_id FROM categories WHERE faculty = ? AND department = ? AND course_code = ?");
                $stmt->bind_param("sss", $faculty, $department, $course_code);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $category_id = $result->fetch_assoc()['category_id'];
                } else {
                    // Create new category
                    $stmt->close();
                    $stmt = $conn->prepare("INSERT INTO categories (faculty, department, course_code, course_name) VALUES (?, ?, ?, ?)");
                    $course_name = $course_code; // Use course code as name if not exists
                    $stmt->bind_param("ssss", $faculty, $department, $course_code, $course_name);
                    $stmt->execute();
                    $category_id = $conn->insert_id;
                }
                $stmt->close();
                
                // Insert into database
                $stmt = $conn->prepare("INSERT INTO resources (user_id, category_id, title, description, file_name, file_path, file_type, file_size, resource_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $original_filename = $file['name'];
                $file_size = $file['size'];
                
                $stmt->bind_param("iisssssss", $user_id, $category_id, $title, $description, $original_filename, $new_filename, $file_ext, $file_size, $resource_type);
                
                if ($stmt->execute()) {
                    $success = 'Resource uploaded successfully!';
                    // Clear form
                    $_POST = [];
                } else {
                    $error = 'Database error. Please try again.';
                    // Delete uploaded file
                    unlink($upload_path);
                }
                
                $stmt->close();
            } else {
                $error = 'Failed to upload file. Please check directory permissions.';
            }
        }
    }
}

closeDBConnection($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Resource - JU Campus Notes Hub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-md sticky top-0 z-50">
        <div class="container mx-auto px-4 py-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-2">
                    <a href="../index.php" class="flex items-center space-x-2">
                        <i class="fas fa-graduation-cap text-3xl text-emerald-600"></i>
                        <span class="text-2xl font-bold text-emerald-900">JU Notes Hub</span>
                    </a>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="../dashboard/user-dashboard.php" class="text-gray-700 hover:text-emerald-600">
                        <i class="fas fa-th-large"></i> Dashboard
                    </a>
                    <a href="browse.php" class="text-gray-700 hover:text-emerald-600">
                        <i class="fas fa-book"></i> Browse
                    </a>
                    <div class="relative">
                        <button id="userMenuButton" class="flex items-center space-x-2 text-gray-700 hover:text-emerald-600">
                            <i class="fas fa-user-circle text-2xl"></i>
                            <span><?php echo htmlspecialchars($user['full_name']); ?></span>
                            <i class="fas fa-chevron-down text-sm"></i>
                        </button>
                        <div id="userMenu" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl py-2 hidden z-50">
                            <a href="../dashboard/profile.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-user"></i> My Profile
                            </a>
                            <a href="../dashboard/settings.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-cog"></i> Settings
                            </a>
                            <a href="../contact.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-envelope"></i> Contact Admin
                            </a>
                            <hr class="my-2">
                            <a href="../auth/logout.php" class="block px-4 py-2 text-red-600 hover:bg-gray-100">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8">
        <div class="max-w-3xl mx-auto">
            <!-- Page Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-800 mb-2">
                    <i class="fas fa-upload"></i> Upload Resource
                </h1>
                <p class="text-gray-600">Share your notes, assignments, and study materials with the community</p>
            </div>

            <!-- Upload Form -->
            <div class="bg-white rounded-xl shadow-md p-8">
                <?php if ($error): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                        <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                        <div class="mt-3">
                            <a href="browse.php" class="text-green-800 underline font-semibold">View all resources</a> or 
                            <a href="upload.php" class="text-green-800 underline font-semibold">upload another</a>
                        </div>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" enctype="multipart/form-data" class="space-y-6">
                    <!-- Title -->
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">
                            Title <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="title" required 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"
                               placeholder="e.g., Data Structures Lecture Notes - Chapter 1"
                               value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>">
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">
                            Description
                        </label>
                        <textarea name="description" rows="4"
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                  placeholder="Provide a brief description of the resource..."><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                    </div>

                    <!-- Faculty -->
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">
                            Faculty <span class="text-red-500">*</span>
                        </label>
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
                        <label class="block text-gray-700 font-semibold mb-2">
                            Department <span class="text-red-500">*</span>
                        </label>
                        <select name="department" id="department" required 
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            <option value="">Select Department</option>
                        </select>
                    </div>

                    <!-- Course Code -->
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">
                            Course Code <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="course_code" required 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"
                               placeholder="e.g., CSE-201"
                               value="<?php echo isset($_POST['course_code']) ? htmlspecialchars($_POST['course_code']) : ''; ?>">
                    </div>

                    <!-- Resource Type -->
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">
                            Resource Type <span class="text-red-500">*</span>
                        </label>
                        <select name="resource_type" required 
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            <option value="">Select Type</option>
                            <option value="notes" <?php echo (isset($_POST['resource_type']) && $_POST['resource_type'] === 'notes') ? 'selected' : ''; ?>>Notes</option>
                            <option value="assignment" <?php echo (isset($_POST['resource_type']) && $_POST['resource_type'] === 'assignment') ? 'selected' : ''; ?>>Assignment</option>
                            <option value="past_questions" <?php echo (isset($_POST['resource_type']) && $_POST['resource_type'] === 'past_questions') ? 'selected' : ''; ?>>Past Questions</option>
                            <option value="lecture_slides" <?php echo (isset($_POST['resource_type']) && $_POST['resource_type'] === 'lecture_slides') ? 'selected' : ''; ?>>Lecture Slides</option>
                            <option value="book" <?php echo (isset($_POST['resource_type']) && $_POST['resource_type'] === 'book') ? 'selected' : ''; ?>>Book</option>
                            <option value="other" <?php echo (isset($_POST['resource_type']) && $_POST['resource_type'] === 'other') ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>

                    <!-- File Upload -->
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">
                            File <span class="text-red-500">*</span>
                        </label>
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-emerald-500 transition">
                            <input type="file" name="file" id="file" required 
                                   class="hidden" 
                                   accept=".pdf,.doc,.docx,.ppt,.pptx,.xlsx,.xls,.jpg,.jpeg,.png,.zip"
                                   onchange="displayFileName(this)">
                            <label for="file" class="cursor-pointer">
                                <i class="fas fa-cloud-upload-alt text-5xl text-gray-400 mb-3"></i>
                                <p class="text-gray-600 mb-2">Click to upload or drag and drop</p>
                                <p class="text-sm text-gray-500">
                                    Supported formats: PDF, DOC, DOCX, PPT, PPTX, XLS, XLSX, JPG, PNG, ZIP<br>
                                    Maximum file size: <?php echo formatFileSize(MAX_FILE_SIZE); ?>
                                </p>
                            </label>
                            <p id="fileName" class="mt-4 text-emerald-600 font-semibold hidden"></p>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex gap-4">
                        <button type="submit" 
                                class="flex-1 bg-emerald-600 text-white font-bold py-3 rounded-lg hover:bg-emerald-700 transition duration-200">
                            <i class="fas fa-upload"></i> Upload Resource
                        </button>
                        <a href="browse.php" 
                           class="flex-1 bg-gray-200 text-gray-700 font-bold py-3 rounded-lg hover:bg-gray-300 transition duration-200 text-center">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>

            <!-- Guidelines -->
            <div class="mt-8 bg-blue-50 border border-blue-200 rounded-xl p-6">
                <h3 class="text-lg font-bold text-blue-900 mb-3">
                    <i class="fas fa-info-circle"></i> Upload Guidelines
                </h3>
                <ul class="space-y-2 text-blue-800">
                    <li><i class="fas fa-check"></i> Ensure your file is relevant and helpful for other students</li>
                    <li><i class="fas fa-check"></i> Use descriptive titles and provide detailed descriptions</li>
                    <li><i class="fas fa-check"></i> Select the correct course and resource type</li>
                    <li><i class="fas fa-check"></i> Only upload content you have the right to share</li>
                    <li><i class="fas fa-check"></i> Avoid uploading copyrighted materials without permission</li>
                </ul>
            </div>
        </div>
    </div>

    <script>
        function displayFileName(input) {
            const fileNameDisplay = document.getElementById('fileName');
            if (input.files && input.files[0]) {
                const file = input.files[0];
                const fileSize = (file.size / 1024 / 1024).toFixed(2);
                fileNameDisplay.textContent = `Selected: ${file.name} (${fileSize} MB)`;
                fileNameDisplay.classList.remove('hidden');
            }
        }

        // Dynamic department loading based on faculty selection
        const facultyDepartments = <?php echo json_encode($faculties); ?>;
        
        document.getElementById('faculty').addEventListener('change', function() {
            const faculty = this.value;
            const departmentSelect = document.getElementById('department');
            
            // Clear existing options
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

        // Trigger change if faculty is already selected (for form resubmission)
        <?php if (isset($_POST['faculty']) && isset($_POST['department'])): ?>
        document.getElementById('faculty').dispatchEvent(new Event('change'));
        document.getElementById('department').value = '<?php echo $_POST['department']; ?>';
        <?php endif; ?>

        // User menu dropdown toggle
        const userMenuButton = document.getElementById('userMenuButton');
        const userMenu = document.getElementById('userMenu');
        
        if (userMenuButton && userMenu) {
            userMenuButton.addEventListener('click', function(e) {
                e.stopPropagation();
                userMenu.classList.toggle('hidden');
            });
            
            // Close menu when clicking outside
            document.addEventListener('click', function(e) {
                if (!userMenu.contains(e.target) && !userMenuButton.contains(e.target)) {
                    userMenu.classList.add('hidden');
                }
            });
        }
    </script>
</body>
</html>
