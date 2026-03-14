<?php
require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/auth.php';

$page_title = 'Add Patient';
$error   = '';
$success = '';
$old     = $_POST; // repopulate form on error

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name  = trim($_POST['first_name'] ?? '');
    $last_name   = trim($_POST['last_name'] ?? '');
    $middle_name = trim($_POST['middle_name'] ?? '');
    $dob         = $_POST['date_of_birth'] ?? '';
    $gender      = $_POST['gender'] ?? '';
    $civil       = $_POST['civil_status'] ?? '';
    $blood       = trim($_POST['blood_type'] ?? '');
    $address     = trim($_POST['address'] ?? '');
    $phone       = trim($_POST['phone'] ?? '');
    $email       = trim($_POST['email'] ?? '');
    $ec_name     = trim($_POST['emergency_contact_name'] ?? '');
    $ec_phone    = trim($_POST['emergency_contact_phone'] ?? '');
    $allergies   = trim($_POST['allergies'] ?? '');
    $med_notes   = trim($_POST['medical_notes'] ?? '');

    // ---- All fields required validation ----
    $missing = [];
    if (!$first_name)  $missing[] = 'First Name';
    if (!$last_name)   $missing[] = 'Last Name';
    if (!$middle_name) $missing[] = 'Middle Name';
    if (!$dob)         $missing[] = 'Date of Birth';
    if (!$gender)      $missing[] = 'Gender';
    if (!$civil)       $missing[] = 'Civil Status';
    if (!$blood)       $missing[] = 'Blood Type';
    if (!$address)     $missing[] = 'Address';
    if (!$phone)       $missing[] = 'Phone';
    if (!$email)       $missing[] = 'Email';
    if (!$ec_name)     $missing[] = 'Emergency Contact Name';
    if (!$ec_phone)    $missing[] = 'Emergency Contact Phone';
    if (!$allergies)   $missing[] = 'Known Allergies';
    if (!$med_notes)   $missing[] = 'Medical Notes';

    if (!empty($missing)) {
        $error = 'Please fill in all required fields: ' . implode(', ', $missing) . '.';
    } elseif (!valid_email($email)) {
        $error = 'Please enter a valid email address.';
    } elseif (!valid_phone($phone)) {
        $error = 'Phone must be in format 09XXXXXXXXX (11 digits).';
    } elseif (!valid_phone($ec_phone)) {
        $error = 'Emergency contact phone must be in format 09XXXXXXXXX.';
    } else {
        $patient_code = generate_code($conn, 'patients', 'PAT');
        $stmt = $conn->prepare("
            INSERT INTO patients
            (patient_code, first_name, last_name, middle_name, date_of_birth, gender, civil_status,
             address, phone, email, emergency_contact_name, emergency_contact_phone,
             blood_type, allergies, medical_notes, registered_by)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
        ");
        $stmt->bind_param(
            'sssssssssssssssi',
            $patient_code, $first_name, $last_name, $middle_name, $dob, $gender, $civil,
            $address, $phone, $email, $ec_name, $ec_phone,
            $blood, $allergies, $med_notes, $current_user_id
        );

        if ($stmt->execute()) {
            $new_id = $conn->insert_id;
            log_action($conn, $current_user_id, $current_user_name, 'Added Patient', 'patients', $new_id, "Patient: $first_name $last_name ($patient_code)");
            $success = "Patient $first_name $last_name ($patient_code) added successfully.";
            $old = []; // clear form on success
        } else {
            $error = 'Failed to save patient. Please try again.';
        }
        $stmt->close();
    }
}

function old($key, $default = '') {
    global $old;
    return htmlspecialchars($old[$key] ?? $default);
}
?>
<!DOCTYPE html>
<html lang="en">
<head><?php include '../../includes/head.php'; ?></head>
<body>
<?php include '../../includes/sidebar.php'; ?>
<div class="main-content">
    <?php include '../../includes/header.php'; ?>
    <div class="page-content">

        <div class="page-header">
            <div>
                <h5>Add New Patient</h5>
                <p>All fields marked <span style="color:var(--danger)">*</span> are required</p>
            </div>
            <a href="list.php" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to List
            </a>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="bi bi-check-circle-fill"></i>
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <i class="bi bi-person-plus" style="color:var(--blue-500)"></i>
                Patient Information
            </div>
            <div class="card-body">
                <form method="POST" id="patientForm" novalidate>

                    <!-- Section: Personal -->
                    <div style="background:var(--gray-50);border-radius:8px;padding:16px 18px;margin-bottom:22px;">
                        <p style="font-family:'Outfit',sans-serif;font-weight:600;font-size:0.85rem;color:var(--blue-600);text-transform:uppercase;letter-spacing:0.06em;margin-bottom:14px;">
                            <i class="bi bi-person"></i> Personal Information
                        </p>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">First Name <span style="color:var(--danger)">*</span></label>
                                <input type="text" name="first_name" class="form-control" required value="<?php echo old('first_name'); ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Last Name <span style="color:var(--danger)">*</span></label>
                                <input type="text" name="last_name" class="form-control" required value="<?php echo old('last_name'); ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Middle Name <span style="color:var(--danger)">*</span></label>
                                <input type="text" name="middle_name" class="form-control" required value="<?php echo old('middle_name'); ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Date of Birth <span style="color:var(--danger)">*</span></label>
                                <input type="date" name="date_of_birth" class="form-control" required value="<?php echo old('date_of_birth'); ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Gender <span style="color:var(--danger)">*</span></label>
                                <select name="gender" class="form-select" required>
                                    <option value="">Select Gender</option>
                                    <option value="male"   <?php echo old('gender') === 'male'   ? 'selected' : ''; ?>>Male</option>
                                    <option value="female" <?php echo old('gender') === 'female' ? 'selected' : ''; ?>>Female</option>
                                    <option value="other"  <?php echo old('gender') === 'other'  ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Civil Status <span style="color:var(--danger)">*</span></label>
                                <select name="civil_status" class="form-select" required>
                                    <option value="">Select Status</option>
                                    <option value="single"    <?php echo old('civil_status') === 'single'    ? 'selected' : ''; ?>>Single</option>
                                    <option value="married"   <?php echo old('civil_status') === 'married'   ? 'selected' : ''; ?>>Married</option>
                                    <option value="widowed"   <?php echo old('civil_status') === 'widowed'   ? 'selected' : ''; ?>>Widowed</option>
                                    <option value="separated" <?php echo old('civil_status') === 'separated' ? 'selected' : ''; ?>>Separated</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Blood Type <span style="color:var(--danger)">*</span></label>
                                <select name="blood_type" class="form-select" required>
                                    <option value="">Select</option>
                                    <?php foreach (['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bt): ?>
                                        <option value="<?php echo $bt; ?>" <?php echo old('blood_type') === $bt ? 'selected' : ''; ?>><?php echo $bt; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Section: Contact -->
                    <div style="background:var(--gray-50);border-radius:8px;padding:16px 18px;margin-bottom:22px;">
                        <p style="font-family:'Outfit',sans-serif;font-weight:600;font-size:0.85rem;color:var(--blue-600);text-transform:uppercase;letter-spacing:0.06em;margin-bottom:14px;">
                            <i class="bi bi-geo-alt"></i> Contact Details
                        </p>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Address <span style="color:var(--danger)">*</span></label>
                                <input type="text" name="address" class="form-control" required placeholder="House No., Street, Barangay, City" value="<?php echo old('address'); ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Phone <span style="color:var(--danger)">*</span></label>
                                <input type="text" name="phone" class="form-control" required placeholder="09XXXXXXXXX" value="<?php echo old('phone'); ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Email <span style="color:var(--danger)">*</span></label>
                                <input type="email" name="email" class="form-control" required placeholder="example@email.com" value="<?php echo old('email'); ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Section: Emergency Contact -->
                    <div style="background:var(--gray-50);border-radius:8px;padding:16px 18px;margin-bottom:22px;">
                        <p style="font-family:'Outfit',sans-serif;font-weight:600;font-size:0.85rem;color:var(--blue-600);text-transform:uppercase;letter-spacing:0.06em;margin-bottom:14px;">
                            <i class="bi bi-telephone"></i> Emergency Contact
                        </p>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Contact Name <span style="color:var(--danger)">*</span></label>
                                <input type="text" name="emergency_contact_name" class="form-control" required value="<?php echo old('emergency_contact_name'); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Contact Phone <span style="color:var(--danger)">*</span></label>
                                <input type="text" name="emergency_contact_phone" class="form-control" required placeholder="09XXXXXXXXX" value="<?php echo old('emergency_contact_phone'); ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Section: Medical -->
                    <div style="background:var(--gray-50);border-radius:8px;padding:16px 18px;margin-bottom:22px;">
                        <p style="font-family:'Outfit',sans-serif;font-weight:600;font-size:0.85rem;color:var(--blue-600);text-transform:uppercase;letter-spacing:0.06em;margin-bottom:14px;">
                            <i class="bi bi-heart-pulse"></i> Medical Background
                        </p>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Known Allergies <span style="color:var(--danger)">*</span></label>
                                <textarea name="allergies" class="form-control" rows="3" required placeholder="List any known drug or material allergies. Type 'None' if none."><?php echo old('allergies'); ?></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Medical Notes <span style="color:var(--danger)">*</span></label>
                                <textarea name="medical_notes" class="form-control" rows="3" required placeholder="Existing conditions, medications, etc. Type 'None' if none."><?php echo old('medical_notes'); ?></textarea>
                            </div>
                        </div>
                    </div>

                    <div style="display:flex;gap:10px;align-items:center;">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg"></i> Save Patient
                        </button>
                        <a href="list.php" class="btn btn-outline-secondary">Cancel</a>
                        <span style="font-size:0.78rem;color:var(--gray-400);margin-left:8px;">
                            <i class="bi bi-info-circle"></i> All fields are required
                        </span>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Extra JS validation - highlight empty fields before submit
document.getElementById('patientForm').addEventListener('submit', function(e) {
    var inputs = this.querySelectorAll('[required]');
    var hasEmpty = false;
    inputs.forEach(function(input) {
        if (!input.value.trim()) {
            input.style.borderColor = 'var(--danger)';
            input.style.boxShadow = '0 0 0 3px rgba(220,38,38,0.12)';
            hasEmpty = true;
        } else {
            input.style.borderColor = '';
            input.style.boxShadow = '';
        }
    });
    if (hasEmpty) {
        e.preventDefault();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
});

// Remove red highlight when user types
document.querySelectorAll('[required]').forEach(function(input) {
    input.addEventListener('input', function() {
        if (this.value.trim()) {
            this.style.borderColor = '';
            this.style.boxShadow = '';
        }
    });
});
</script>
</body>
</html>
