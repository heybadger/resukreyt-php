<?php
session_start();
require_once 'connection.php';

$action = isset($_GET['action']) ? $_GET['action'] : 'create';
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$resume = null;
$result = mysqli_query($conn, "SELECT * FROM resumes ORDER BY id DESC LIMIT 1");
if ($result && mysqli_num_rows($result) > 0) {
    $resume = mysqli_fetch_assoc($result);
    $id = $resume['id'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create'])) {
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone'] ?? '');
    $location = mysqli_real_escape_string($conn, $_POST['location'] ?? '');
    $social1 = mysqli_real_escape_string($conn, $_POST['social1'] ?? '');
    $social2 = mysqli_real_escape_string($conn, $_POST['social2'] ?? '');
    $social3 = mysqli_real_escape_string($conn, $_POST['social3'] ?? '');
    $summary = mysqli_real_escape_string($conn, $_POST['summary'] ?? '');
    $experience = mysqli_real_escape_string($conn, $_POST['experience'] ?? '');
    $education = mysqli_real_escape_string($conn, $_POST['education'] ?? '');
    $skills = mysqli_real_escape_string($conn, $_POST['skills'] ?? '');
    $projects = mysqli_real_escape_string($conn, $_POST['projects'] ?? '');
    $certifications = mysqli_real_escape_string($conn, $_POST['certifications'] ?? '');
    $template = mysqli_real_escape_string($conn, $_POST['template'] ?? 'resume');

    $social_data = json_encode([
        'social1' => $social1,
        'social2' => $social2,
        'social3' => $social3
    ]);

    $sql = "INSERT INTO resumes (full_name, title, email, phone, location, social, summary, experience, education, skills, projects, certifications, template) 
            VALUES ('$full_name', '$title', '$email', '$phone', '$location', '$social_data', '$summary', '$experience', '$education', '$skills', '$projects', '$certifications', '$template')";

    if (mysqli_query($conn, $sql)) {
        $new_id = mysqli_insert_id($conn);
        mysqli_query($conn, "INSERT INTO resume_analytics (resume_id) VALUES ($new_id)");
        header("Location: index.php?action=view&id=$new_id");
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $location = mysqli_real_escape_string($conn, $_POST['location']);
    $social1 = mysqli_real_escape_string($conn, $_POST['social1'] ?? '');
    $social2 = mysqli_real_escape_string($conn, $_POST['social2'] ?? '');
    $social3 = mysqli_real_escape_string($conn, $_POST['social3'] ?? '');
    $summary = mysqli_real_escape_string($conn, $_POST['summary'] ?? '');
    $experience = mysqli_real_escape_string($conn, $_POST['experience'] ?? '');
    $education = mysqli_real_escape_string($conn, $_POST['education'] ?? '');
    $skills = mysqli_real_escape_string($conn, $_POST['skills'] ?? '');
    $projects = mysqli_real_escape_string($conn, $_POST['projects'] ?? '');
    $certifications = mysqli_real_escape_string($conn, $_POST['certifications'] ?? '');
    $template = mysqli_real_escape_string($conn, $_POST['template'] ?? 'resume');

    $social_data = json_encode([
        'social1' => $social1,
        'social2' => $social2,
        'social3' => $social3
    ]);

    $sql = "UPDATE resumes SET full_name='$full_name', title='$title', email='$email', phone='$phone', location='$location', social='$social_data', summary='$summary', experience='$experience', education='$education', skills='$skills', projects='$projects', certifications='$certifications', template='$template' WHERE id=$id";

    if (mysqli_query($conn, $sql)) {
        header("Location: index.php?action=view&id=$id");
        exit();
    }
}

if (isset($_GET['delete'])) {
    $del_id = (int) $_GET['delete'];
    mysqli_query($conn, "DELETE FROM resume_analytics WHERE resume_id = $del_id");
    mysqli_query($conn, "DELETE FROM resumes WHERE id = $del_id");
    header("Location: index.php");
    exit();
}

if ($id > 0 && ($action === 'view' || $action === 'edit')) {
    $result = mysqli_query($conn, "SELECT * FROM resumes WHERE id = $id");
    $resume = mysqli_fetch_assoc($result);

    if (!$resume) {
        $action = 'create';
    }

    if ($action === 'view' && $resume) {
        mysqli_query($conn, "UPDATE resume_analytics SET views = views + 1, last_viewed = NOW() WHERE resume_id = $id");
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ResuKreyt</title>
    <script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        :root {
            --bg-primary: #1e1e1e;
            --bg-secondary: #252526;
            --bg-card: #2d2d2d;
            --bg-hover: #3a3a3a;
            --border-color: #3e3e3e;
            --text-primary: #d4d4d4;
            --text-secondary: #cccccc;
            --text-muted: #858585;
            --accent: #007acc;
            --accent-hover: #1a8bd6;
            --success: #4ec9b0;
            --warning: #d7ba7d;
            --danger: #f44747;
        }

        [data-theme="white"] {
            --bg-primary: #ffffff;
            --bg-secondary: #f5f5f5;
            --bg-card: #ffffff;
            --bg-hover: #e8e8e8;
            --border-color: #d4d4d4;
            --text-primary: #1e1e1e;
            --text-secondary: #333333;
            --text-muted: #666666;
            --accent: #a84a64;
            --accent-hover: #8a3a54;
            --success: #2e7d32;
            --warning: #f57c00;
            --danger: #c62828;
        }

        [data-theme="dark"] {
            --bg-primary: #1e1e1e;
            --bg-secondary: #252526;
            --bg-card: #2d2d2d;
            --bg-hover: #3a3a3a;
            --border-color: #3e3e3e;
            --text-primary: #d4d4d4;
            --text-secondary: #cccccc;
            --text-muted: #858585;
            --accent: #007acc;
            --accent-hover: #1a8bd6;
            --success: #4ec9b0;
            --warning: #d7ba7d;
            --danger: #f44747;
        }

        [data-theme="pink"] {
            --bg-primary: #e8d0dc;
            --bg-secondary: #fcf5f8;
            --bg-card: #fdf5f8;
            --bg-hover: #f5e8ee;
            --border-color: #d8b8c8;
            --text-primary: #3a1a2a;
            --text-secondary: #6a4a5a;
            --text-muted: #8a6a7a;
            --accent: #a84a64;
            --accent-hover: #8a3a54;
            --success: #2e7d32;
            --warning: #f57c00;
            --danger: #c62828;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, 'Segoe UI', 'Monaco', 'Consolas', monospace;
            background: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
            padding: 20px;
            transition: background 0.3s, color 0.3s;
        }

        .app-container {
            max-width: 1000px;
            margin: 0 auto;
            background: var(--bg-secondary);
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid var(--border-color);
            min-height: 90vh;
            transition: background 0.3s, border-color 0.3s;
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 24px;
            border-bottom: 1px solid var(--border-color);
            flex-wrap: wrap;
            gap: 12px;
            transition: border-color 0.3s;
        }

        .top-bar .left {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .top-bar .logo {
            font-size: 20px;
            font-weight: 700;
            color: var(--accent);
            font-family: 'Consolas', monospace;
            transition: color 0.3s;
        }

        .top-bar .logo span {
            color: var(--accent);
            transition: color 0.3s;
        }

        .top-bar .title {
            font-size: 13px;
            color: var(--text-muted);
            font-family: 'Consolas', monospace;
            transition: color 0.3s;
        }

        .top-bar .actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            align-items: center;
        }

        .theme-selector {
            display: flex;
            gap: 4px;
            background: var(--bg-primary);
            padding: 3px;
            border-radius: 6px;
            border: 1px solid var(--border-color);
            transition: background 0.3s, border-color 0.3s;
        }

        .theme-selector .theme-btn {
            width: 26px;
            height: 26px;
            border-radius: 4px;
            border: 2px solid transparent;
            cursor: pointer;
            transition: all 0.2s;
            padding: 0;
        }

        .theme-selector .theme-btn:hover {
            transform: scale(1.1);
        }

        .theme-selector .theme-btn.active {
            border-color: var(--accent);
        }

        .theme-btn[data-theme="white"] {
            background: #ffffff;
            border-color: #d4d4d4;
        }

        .theme-btn[data-theme="dark"] {
            background: #1e1e1e;
            border-color: #3e3e3e;
        }

        .theme-btn[data-theme="pink"] {
            background: #a84a64;
            border-color: #a84a64;
        }

        .btn {
            padding: 6px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 500;
            font-family: inherit;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            text-decoration: none;
        }

        .btn-primary {
            background: var(--accent);
            color: var(--bg-primary);
        }

        .btn-primary:hover {
            background: var(--accent-hover);
        }

        .btn-secondary {
            background: var(--bg-hover);
            color: var(--text-primary);
        }

        .btn-secondary:hover {
            background: var(--border-color);
        }

        .btn-success {
            background: var(--success);
            color: var(--bg-primary);
        }

        .btn-success:hover {
            opacity: 0.8;
        }

        .btn-warning {
            background: var(--warning);
            color: var(--bg-primary);
        }

        .btn-warning:hover {
            opacity: 0.8;
        }

        .btn-danger {
            background: var(--danger);
            color: var(--bg-primary);
        }

        .btn-danger:hover {
            opacity: 0.8;
        }

        .btn-sm {
            padding: 4px 10px;
            font-size: 12px;
        }

        .btn-block {
            width: 100%;
            justify-content: center;
            padding: 10px;
            font-size: 15px;
        }

        .content {
            padding: 24px;
            max-height: 85vh;
            overflow-y: auto;
        }

        .form-container {
            background: var(--bg-card);
            border-radius: 8px;
            padding: 24px;
            border: 1px solid var(--border-color);
            max-width: 800px;
            margin: 0 auto;
            transition: background 0.3s, border-color 0.3s;
        }

        .form-container h2 {
            color: var(--text-primary);
            margin-bottom: 20px;
            font-weight: 600;
            font-size: 20px;
            transition: color 0.3s;
        }

        .form-container h2 span {
            color: var(--accent);
            transition: color 0.3s;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .form-group {
            margin-bottom: 4px;
        }

        .form-group label {
            display: block;
            margin-bottom: 4px;
            font-size: 13px;
            font-weight: 500;
            color: var(--text-secondary);
            transition: color 0.3s;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 8px 12px;
            background: var(--bg-primary);
            border: 1px solid var(--border-color);
            border-radius: 4px;
            color: var(--text-primary);
            font-size: 14px;
            font-family: inherit;
            transition: border-color 0.2s, background 0.3s, color 0.3s;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--accent);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 60px;
        }

        .full-width {
            grid-column: 1 / -1;
        }

        .hint {
            font-size: 11px;
            color: var(--text-muted);
            margin-top: 4px;
            transition: color 0.3s;
        }

        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            border-top: 1px solid var(--border-color);
            padding-top: 20px;
            transition: border-color 0.3s;
        }

        .resume-view.cv {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12px;
            background: #ffffff;
            border-radius: 8px;
            padding: 50px 45px;
            max-width: 900px;
            margin: 0 auto;
            color: #1e1e2e;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            line-height: 1.5;
        }

        .resume-view.cv .header {
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 2px solid #1e1e2e;
            margin-bottom: 20px;
        }

        .resume-view.cv .header h1 {
            font-size: 28px;
            color: #1e1e2e;
            letter-spacing: 2px;
        }

        .resume-view.cv .header h2 {
            font-size: 16px;
            color: #555;
            font-weight: normal;
            margin: 4px 0;
        }

        .resume-view.cv .contact span {
            margin: 0 10px;
            color: #666;
        }

        .resume-view.cv .social a {
            margin: 0 10px;
            color: #667eea;
            text-decoration: none;
        }

        .resume-view.cv .section {
            margin-top: 20px;
        }

        .resume-view.cv .section h3 {
            color: #1e1e2e;
            border-bottom: 1px solid #ccc;
            padding-bottom: 4px;
            margin-bottom: 8px;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .resume-view.cv .section .content {
            line-height: 1.6;
            color: #333;
        }

        .resume-view.cv .skills {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .resume-view.cv .skill-tag {
            background: #e8e8e8;
            color: #1e1e2e;
            padding: 3px 14px;
            border-radius: 4px;
            font-size: 12px;
        }

        .resume-view.resume {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 11px;
            background: #ffffff;
            border-radius: 8px;
            padding: 35px 35px;
            max-width: 900px;
            margin: 0 auto;
            color: #1e1e2e;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            line-height: 1.4;
        }

        .resume-view.resume .header {
            text-align: center;
            padding-bottom: 16px;
            border-bottom: 2px solid #667eea;
            margin-bottom: 16px;
        }

        .resume-view.resume .header h1 {
            font-size: 26px;
            color: #1e1e2e;
        }

        .resume-view.resume .header h2 {
            font-size: 16px;
            color: #667eea;
            font-weight: 500;
            margin: 4px 0;
        }

        .resume-view.resume .contact span {
            margin: 0 8px;
            color: #666;
        }

        .resume-view.resume .social a {
            margin: 0 8px;
            color: #667eea;
            text-decoration: none;
        }

        .resume-view.resume .section {
            margin-top: 16px;
        }

        .resume-view.resume .section h3 {
            color: #1e1e2e;
            border-bottom: 2px solid #667eea;
            padding-bottom: 4px;
            margin-bottom: 8px;
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .resume-view.resume .section .content {
            line-height: 1.5;
            color: #333;
        }

        .resume-view.resume .skills {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .resume-view.resume .skill-tag {
            background: #667eea;
            color: white;
            padding: 3px 12px;
            border-radius: 12px;
            font-size: 11px;
        }

        .resume-view.ats {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            background: #ffffff;
            border-radius: 0;
            padding: 30px 35px;
            max-width: 900px;
            margin: 0 auto;
            color: #1e1e2e;
            line-height: 1.4;
            border: 1px solid #ccc;
        }

        .resume-view.ats .header {
            text-align: left;
            padding-bottom: 10px;
            border-bottom: 1px solid #1e1e2e;
            margin-bottom: 12px;
        }

        .resume-view.ats .header h1 {
            font-size: 20px;
            color: #1e1e2e;
        }

        .resume-view.ats .header h2 {
            font-size: 13px;
            color: #1e1e2e;
            font-weight: normal;
            margin: 2px 0;
        }

        .resume-view.ats .contact {
            margin: 2px 0;
        }

        .resume-view.ats .contact span {
            display: inline-block;
            margin: 0 4px;
            color: #1e1e2e;
            font-size: 11px;
        }

        .resume-view.ats .social {
            margin: 2px 0;
        }

        .resume-view.ats .social a {
            display: inline-block;
            margin: 0 4px;
            color: #1e1e2e;
            font-size: 11px;
        }

        .resume-view.ats .section {
            margin-top: 10px;
        }

        .resume-view.ats .section h3 {
            color: #1e1e2e;
            border-bottom: 1px solid #1e1e2e;
            padding-bottom: 2px;
            margin-bottom: 4px;
            font-weight: 700;
            font-size: 12px;
            text-transform: uppercase;
        }

        .resume-view.ats .section .content {
            line-height: 1.5;
            color: #1e1e2e;
            font-size: 11px;
        }

        .resume-view.ats .skills {
            display: flex;
            flex-wrap: wrap;
            gap: 3px;
        }

        .resume-view.ats .skill-tag {
            background: transparent;
            color: #1e1e2e;
            padding: 1px 6px;
            border: 1px solid #1e1e2e;
            border-radius: 0;
            font-size: 10px;
            display: inline-block;
            margin: 1px 0;
        }

        .resume-view .contact {
            margin-top: 4px;
        }

        .resume-view .social {
            margin-top: 2px;
        }

        .resume-view .footer-info {
            display: none !important;
        }

        .view-actions {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 10px 20px;
            border-radius: 4px;
            color: var(--bg-primary);
            font-weight: 500;
            font-size: 14px;
            background: var(--success);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            z-index: 2000;
            transform: translateX(400px);
            transition: transform 0.3s;
            font-family: inherit;
        }

        .toast.show {
            transform: translateX(0);
        }

        .toast-error {
            background: var(--danger);
        }

        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: var(--bg-primary);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--border-color);
            border-radius: 3px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--bg-hover);
        }

        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(4px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            flex-direction: column;
        }

        .loading-overlay.active {
            display: flex;
        }

        .loading-overlay .spinner {
            width: 50px;
            height: 50px;
            border: 4px solid var(--border-color);
            border-top-color: var(--accent);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .loading-overlay p {
            color: var(--text-primary);
            margin-top: 16px;
            font-size: 16px;
            font-family: inherit;
        }

        @media (max-width: 768px) {
            .top-bar {
                flex-direction: column;
                align-items: stretch;
            }

            .top-bar .left {
                flex-wrap: wrap;
            }

            .top-bar .actions {
                justify-content: center;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .content {
                padding: 16px;
            }

            .resume-view.cv,
            .resume-view.resume,
            .resume-view.ats {
                padding: 20px;
            }

            .resume-view .contact span {
                display: block;
                margin: 4px 0;
            }
        }
    </style>
</head>

<body>

    <div id="toast" class="toast"></div>

    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner"></div>
        <p>Generating your resume...</p>
    </div>

    <div class="app-container">
        <div class="top-bar">
            <div class="left">
                <div class="logo">> <span>Resu</span>Kreyt</div>
            </div>
            <div class="actions">
                <div class="theme-selector">
                    <button class="theme-btn" data-theme="white" title="White"
                        style="background: #ffffff; border-color: #d4d4d4;"></button>
                    <button class="theme-btn" data-theme="dark" title="VS Code Dark"
                        style="background: #1e1e1e; border-color: #3e3e3e;"></button>
                    <button class="theme-btn" data-theme="pink" title="Pink"
                        style="background: #a84a64; border-color: #a84a64;"></button>
                </div>

                <?php if ($action === 'view' && $resume): ?>
                    <a href="index.php?action=edit&id=<?php echo $id; ?>" class="btn btn-warning">Edit</a>
                    <button onclick="saveAsPDF(<?php echo $id; ?>)" class="btn btn-success">PDF</button>
                    <button onclick="saveAsImage(<?php echo $id; ?>)" class="btn btn-primary">Save Image</button>
                    <button onclick="window.print()" class="btn btn-secondary">Print</button>
                    <a href="index.php?delete=<?php echo $id; ?>" class="btn btn-danger"
                        onclick="return confirm('Delete this resume?')">Delete</a>
                <?php endif; ?>

                <?php if ($action === 'edit' && $resume): ?>
                    <a href="index.php?action=view&id=<?php echo $id; ?>" class="btn btn-secondary">Cancel</a>
                <?php endif; ?>

                <?php if ($action === 'create'): ?>
                    <span style="font-size:12px;color:var(--text-muted);font-family:'Consolas',monospace;"></span>
                <?php endif; ?>
            </div>
        </div>

        <div class="content">

            <?php if ($action === 'create'): ?>
                <div class="form-container">

                    <form method="POST">
                        <input type="hidden" name="create" value="1">
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Full Name</label>
                                <input type="text" name="full_name" required placeholder="Your Name">
                            </div>
                            <div class="form-group">
                                <label>Professional Title</label>
                                <input type="text" name="title" required placeholder="Job Title">
                            </div>
                            <div class="form-group">
                                <label>Email Address</label>
                                <input type="email" name="email" required placeholder="Enter Your Active Email">
                            </div>
                            <div class="form-group">
                                <label>Phone Number</label>
                                <input type="text" name="phone" placeholder="+63 9056 612">
                            </div>
                            <div class="form-group">
                                <label>Location</label>
                                <input type="text" name="address" placeholder="City, Country">
                            </div>
                            <div class="form-group">
                                <label>LinkedIn Profile</label>
                                <input type="text" name="social1" placeholder="linkedin.com/in/username">
                            </div>
                            <div class="form-group">
                                <label>GitHub Profile</label>
                                <input type="text" name="social2" placeholder="github.com/username">
                            </div>
                            <div class="form-group">
                                <label>Portfolio / Website</label>
                                <input type="text" name="social3" placeholder="yourwebsite.com">
                            </div>
                            <div class="form-group full-width">
                                <label>Professional Summary</label>
                                <textarea name="summary" rows="4"
                                    placeholder="Brief overview of your professional background"></textarea>
                            </div>
                            <div class="form-group full-width">
                                <label>Work Experience</label>
                                <textarea name="experience" rows="6" placeholder="Place Work Experience Here"></textarea>
                            </div>
                            <div class="form-group full-width">
                                <label>Education</label>
                                <textarea name="education" rows="4"
                                    placeholder="Place Educational Background Here"></textarea>
                            </div>
                            <div class="form-group full-width">
                                <label>Skills</label>
                                <textarea name="skills" rows="3" placeholder="Place Skills Here"></textarea>
                            </div>
                            <div class="form-group full-width">
                                <label>Projects</label>
                                <textarea name="projects" rows="4" placeholder="Place Projects Here"></textarea>
                            </div>
                            <div class="form-group full-width">
                                <label>Certifications & Training</label>
                                <textarea name="certifications" rows="3" placeholder="Place Certificates Here"></textarea>
                            </div>
                            <div class="form-group">
                                <label>Template Style</label>
                                <select name="template">
                                    <option value="cv">CV</option>
                                    <option value="resume" selected>Resume</option>
                                    <option value="ats">ATS Friendly</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary btn-block">Create Resume</button>
                        </div>
                    </form>
                </div>

            <?php elseif ($action === 'view' && $resume): ?>
                <?php
                $social_data = [];
                if (isset($resume['social']) && !empty($resume['social'])) {
                    $social_data = json_decode($resume['social'], true);
                }
                if (!$social_data) {
                    $social_data = ['social1' => '', 'social2' => '', 'social3' => ''];
                }
                $social_links = array_filter([$social_data['social1'] ?? '', $social_data['social2'] ?? '', $social_data['social3'] ?? '']);
                ?>
                <div class="view-actions">
                    <a href="index.php?action=edit&id=<?php echo $id; ?>" class="btn btn-warning">Edit</a>
                    <button onclick="saveAsPDF(<?php echo $id; ?>)" class="btn btn-success">Download PDF</button>
                    <button onclick="saveAsImage(<?php echo $id; ?>)" class="btn btn-primary">Save as Image</button>
                    <button onclick="window.print()" class="btn btn-secondary">Print</button>
                    <a href="index.php?delete=<?php echo $id; ?>" class="btn btn-danger"
                        onclick="return confirm('Delete this resume?')">Delete</a>
                    <span
                        style="display:inline-block;padding:2px 10px;border-radius:10px;font-size:11px;font-weight:500;background:var(--bg-hover);color:var(--text-secondary);font-family:'Consolas',monospace;"><?php echo strtoupper($resume['template']); ?></span>
                </div>

                <div class="resume-view <?php echo $resume['template']; ?>" id="resume-content">
                    <div class="header">
                        <h1><?php echo htmlspecialchars($resume['full_name']); ?></h1>
                        <h2><?php echo htmlspecialchars($resume['title']); ?></h2>
                        <div class="contact">
                            <?php if ($resume['email']): ?><span><?php echo htmlspecialchars($resume['email']); ?></span><?php endif; ?>
                            <?php if ($resume['phone']): ?><span><?php echo htmlspecialchars($resume['phone']); ?></span><?php endif; ?>
                            <?php if ($resume['location']): ?><span><?php echo htmlspecialchars($resume['location']); ?></span><?php endif; ?>
                        </div>
                        <?php if (!empty($social_links)): ?>
                            <div class="social">
                                <?php foreach ($social_links as $link): ?>
                                    <?php if ($link): ?>
                                        <a href="https://<?php echo htmlspecialchars($link); ?>"
                                            target="_blank"><?php echo htmlspecialchars($link); ?></a>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if ($resume['summary']): ?>
                        <div class="section">
                            <h3>Professional Summary</h3>
                            <div class="content"><?php echo nl2br(htmlspecialchars($resume['summary'])); ?></div>
                        </div>
                    <?php endif; ?>

                    <?php if ($resume['experience']): ?>
                        <div class="section">
                            <h3>Work Experience</h3>
                            <div class="content"><?php echo nl2br(htmlspecialchars($resume['experience'])); ?></div>
                        </div>
                    <?php endif; ?>

                    <?php if ($resume['education']): ?>
                        <div class="section">
                            <h3>Education</h3>
                            <div class="content"><?php echo nl2br(htmlspecialchars($resume['education'])); ?></div>
                        </div>
                    <?php endif; ?>

                    <?php if ($resume['skills']): ?>
                        <div class="section">
                            <h3>Skills</h3>
                            <div class="skills">
                                <?php foreach (explode(',', $resume['skills']) as $skill):
                                    $skill = trim($skill);
                                    if ($skill): ?>
                                        <span class="skill-tag"><?php echo htmlspecialchars($skill); ?></span>
                                    <?php endif; endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($resume['projects']): ?>
                        <div class="section">
                            <h3>Projects</h3>
                            <div class="content"><?php echo nl2br(htmlspecialchars($resume['projects'])); ?></div>
                        </div>
                    <?php endif; ?>

                    <?php if ($resume['certifications']): ?>
                        <div class="section">
                            <h3>Certifications & Training</h3>
                            <div class="content"><?php echo nl2br(htmlspecialchars($resume['certifications'])); ?></div>
                        </div>
                    <?php endif; ?>
                </div>

            <?php elseif ($action === 'edit' && $resume): ?>
                <?php
                $social_data = [];
                if (isset($resume['social']) && !empty($resume['social'])) {
                    $social_data = json_decode($resume['social'], true);
                }
                if (!$social_data) {
                    $social_data = ['social1' => '', 'social2' => '', 'social3' => ''];
                }
                ?>
                <div class="form-container">
                    <h2>Edit <span>Resume</span></h2>
                    <form method="POST">
                        <input type="hidden" name="update" value="1">
                        <div class="form-grid">
                            <div class="form-group"><label>Full Name</label><input type="text" name="full_name"
                                    value="<?php echo htmlspecialchars($resume['full_name']); ?>" required></div>
                            <div class="form-group"><label>Professional Title</label><input type="text" name="title"
                                    value="<?php echo htmlspecialchars($resume['title']); ?>" required></div>
                            <div class="form-group"><label>Email Address</label><input type="email" name="email"
                                    value="<?php echo htmlspecialchars($resume['email']); ?>" required></div>
                            <div class="form-group"><label>Phone Number</label><input type="text" name="phone"
                                    value="<?php echo htmlspecialchars($resume['phone']); ?>"></div>
                            <div class="form-group"><label>Location</label><input type="text" name="location"
                                    value="<?php echo htmlspecialchars($resume['location']); ?>"></div>
                            <div class="form-group"><label>LinkedIn Profile</label><input type="text" name="social1"
                                    value="<?php echo htmlspecialchars($social_data['social1'] ?? ''); ?>"></div>
                            <div class="form-group"><label>GitHub Profile</label><input type="text" name="social2"
                                    value="<?php echo htmlspecialchars($social_data['social2'] ?? ''); ?>"></div>
                            <div class="form-group"><label>Portfolio / Website</label><input type="text" name="social3"
                                    value="<?php echo htmlspecialchars($social_data['social3'] ?? ''); ?>"></div>
                            <div class="form-group full-width"><label>Professional Summary</label><textarea name="summary"
                                    rows="4"><?php echo htmlspecialchars($resume['summary']); ?></textarea></div>
                            <div class="form-group full-width"><label>Work Experience</label><textarea name="experience"
                                    rows="6"><?php echo htmlspecialchars($resume['experience']); ?></textarea></div>
                            <div class="form-group full-width"><label>Education</label><textarea name="education"
                                    rows="4"><?php echo htmlspecialchars($resume['education']); ?></textarea></div>
                            <div class="form-group full-width"><label>Skills</label><textarea name="skills"
                                    rows="3"><?php echo htmlspecialchars($resume['skills']); ?></textarea></div>
                            <div class="form-group full-width"><label>Projects</label><textarea name="projects"
                                    rows="4"><?php echo htmlspecialchars($resume['projects']); ?></textarea></div>
                            <div class="form-group full-width"><label>Certifications & Training</label><textarea
                                    name="certifications"
                                    rows="3"><?php echo htmlspecialchars($resume['certifications']); ?></textarea></div>
                            <div class="form-group"><label>Template Style</label>
                                <select name="template">
                                    <option value="cv" <?php echo $resume['template'] == 'cv' ? 'selected' : ''; ?>>CV
                                    </option>
                                    <option value="resume" <?php echo $resume['template'] == 'resume' ? 'selected' : ''; ?>>
                                        Resume</option>
                                    <option value="ats" <?php echo $resume['template'] == 'ats' ? 'selected' : ''; ?>>ATS
                                        Friendly</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-actions">
                            <a href="index.php?action=view&id=<?php echo $id; ?>" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary btn-block">Save Resume</button>
                        </div>
                    </form>
                </div>

            <?php else: ?>
                <div class="form-container">
                    <h2>Create <span>Resume</span></h2>

                    <form method="POST">
                        <input type="hidden" name="create" value="1">
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Full Name</label>
                                <input type="text" name="full_name" required placeholder="John Doe">
                            </div>
                            <div class="form-group">
                                <label>Professional Title</label>
                                <input type="text" name="title" required placeholder="Senior Software Engineer">
                            </div>
                            <div class="form-group">
                                <label>Email Address</label>
                                <input type="email" name="email" required placeholder="john@example.com">
                            </div>
                            <div class="form-group">
                                <label>Phone Number</label>
                                <input type="text" name="phone" placeholder="+1 (555) 123-4567">
                            </div>
                            <div class="form-group">
                                <label>Location</label>
                                <input type="text" name="location" placeholder="San Francisco, CA">
                            </div>
                            <div class="form-group">
                                <label>LinkedIn Profile</label>
                                <input type="text" name="social1" placeholder="linkedin.com/in/username">
                            </div>
                            <div class="form-group">
                                <label>GitHub Profile</label>
                                <input type="text" name="social2" placeholder="github.com/username">
                            </div>
                            <div class="form-group">
                                <label>Portfolio / Website</label>
                                <input type="text" name="social3" placeholder="yourwebsite.com">
                            </div>
                            <div class="form-group full-width">
                                <label>Professional Summary</label>
                                <textarea name="summary" rows="4"
                                    placeholder="Brief overview of your professional background..."></textarea>
                            </div>
                            <div class="form-group full-width">
                                <label>Work Experience</label>
                                <textarea name="experience" rows="6"
                                    placeholder="Job Title | Company | Dates&#10;Description of responsibilities and achievements"></textarea>
                            </div>
                            <div class="form-group full-width">
                                <label>Education</label>
                                <textarea name="education" rows="4"
                                    placeholder="Degree | Institution | Year&#10;Relevant coursework or honors"></textarea>
                            </div>
                            <div class="form-group full-width">
                                <label>Skills</label>
                                <textarea name="skills" rows="3"
                                    placeholder="e.g. Project Management, Data Analysis, Communication"></textarea>
                            </div>
                            <div class="form-group full-width">
                                <label>Projects</label>
                                <textarea name="projects" rows="4"
                                    placeholder="Project Name | Description | Technologies Used"></textarea>
                            </div>
                            <div class="form-group full-width">
                                <label>Certifications & Training</label>
                                <textarea name="certifications" rows="3"
                                    placeholder="Certification Name | Issuing Organization | Year"></textarea>
                            </div>
                            <div class="form-group">
                                <label>Template Style</label>
                                <select name="template">
                                    <option value="cv">CV</option>
                                    <option value="resume" selected>Resume</option>
                                    <option value="ats">ATS Friendly</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary btn-block">Create Resume</button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        document.querySelectorAll('.theme-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                document.querySelectorAll('.theme-btn').forEach(function (b) { b.classList.remove('active'); });
                this.classList.add('active');
                document.documentElement.setAttribute('data-theme', this.dataset.theme);
                localStorage.setItem('theme', this.dataset.theme);
            });
        });

        var savedTheme = localStorage.getItem('theme');
        if (savedTheme) {
            document.documentElement.setAttribute('data-theme', savedTheme);
            document.querySelectorAll('.theme-btn').forEach(function (btn) {
                if (btn.dataset.theme === savedTheme) btn.classList.add('active');
            });
        } else {
            document.documentElement.setAttribute('data-theme', 'dark');
            document.querySelector('.theme-btn[data-theme="dark"]').classList.add('active');
        }

        function showToast(msg, type) {
            if (type === undefined) type = 'success';
            var t = document.getElementById('toast');
            t.textContent = msg;
            t.className = 'toast';
            if (type === 'error') t.className = 'toast toast-error';
            setTimeout(function () { t.classList.add('show'); }, 10);
            setTimeout(function () { t.classList.remove('show'); }, 3000);
        }

        function showLoading() {
            document.getElementById('loadingOverlay').classList.add('active');
        }

        function hideLoading() {
            document.getElementById('loadingOverlay').classList.remove('active');
        }

        function saveAsPDF(resumeId) {
            var element = document.getElementById('resume-content');
            if (!element) {
                showToast('No resume to export.', 'error');
                return;
            }
            showLoading();

            var clone = element.cloneNode(true);
            clone.style.background = '#ffffff';
            clone.style.color = '#1e1e2e';
            clone.style.padding = '40px';
            clone.style.borderRadius = '0';
            clone.style.boxShadow = 'none';
            clone.style.fontFamily = 'Arial, sans-serif';

            var allElements = clone.querySelectorAll('*');
            for (var i = 0; i < allElements.length; i++) {
                var el = allElements[i];
                if (el.style.background === 'var(--bg-primary)') el.style.background = '#ffffff';
                if (el.style.color === 'var(--text-primary)') el.style.color = '#1e1e2e';
            }

            var container = document.createElement('div');
            container.style.position = 'fixed';
            container.style.left = '-9999px';
            container.style.top = '0';
            container.style.background = '#ffffff';
            container.appendChild(clone);
            document.body.appendChild(container);

            var opt = {
                margin: 10,
                filename: 'resume-' + new Date().toISOString().slice(0, 10) + '.pdf',
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2, useCORS: true, logging: false, backgroundColor: '#ffffff' },
                jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
            };

            html2pdf().set(opt).from(clone).save().then(function () {
                document.body.removeChild(container);
                hideLoading();
                showToast('PDF downloaded successfully!');
            }).catch(function (err) {
                document.body.removeChild(container);
                hideLoading();
                showToast('Error generating PDF: ' + err.message, 'error');
            });
        }

        function saveAsImage(resumeId) {
            var element = document.getElementById('resume-content');
            if (!element) {
                showToast('No resume to save.', 'error');
                return;
            }
            showLoading();

            var clone = element.cloneNode(true);
            clone.style.background = '#ffffff';
            clone.style.color = '#1e1e2e';
            clone.style.padding = '40px';
            clone.style.borderRadius = '0';
            clone.style.boxShadow = 'none';
            clone.style.fontFamily = 'Arial, sans-serif';

            var allElements = clone.querySelectorAll('*');
            for (var i = 0; i < allElements.length; i++) {
                var el = allElements[i];
                if (el.style.background === 'var(--bg-primary)') el.style.background = '#ffffff';
                if (el.style.color === 'var(--text-primary)') el.style.color = '#1e1e2e';
            }

            var container = document.createElement('div');
            container.style.position = 'fixed';
            container.style.left = '-9999px';
            container.style.top = '0';
            container.style.background = '#ffffff';
            container.appendChild(clone);
            document.body.appendChild(container);

            html2canvas(clone, {
                scale: 3,
                backgroundColor: '#ffffff',
                allowTaint: false,
                useCORS: true,
                logging: false
            }).then(function (canvas) {
                document.body.removeChild(container);
                var link = document.createElement('a');
                var name = document.querySelector('.resume-view .header h1');
                var fileName = name ? name.textContent : 'resume';
                link.download = fileName.toLowerCase().replace(/\s+/g, '_') + '_' + new Date().toISOString().slice(0, 10) + '.png';
                link.href = canvas.toDataURL('image/png', 1.0);
                link.click();
                hideLoading();
                showToast('Image saved successfully!');
            }).catch(function (err) {
                document.body.removeChild(container);
                hideLoading();
                showToast('Error saving image: ' + err.message, 'error');
            });
        }
    </script>

</body>

</html>