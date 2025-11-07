<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration - WOLKITE POLYTECHNIC COLLEGE </title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="light-mode">
    <?php include 'navigation.php'; ?>

    <div class="theme-toggle">
        <button id="themeToggle" class="theme-btn">
            <i class="fas fa-moon"></i>
        </button>
    </div>

    <div class="container">
        <div class="header">
            <div class="college-brand">
                <div class="college-logo">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <div class="college-info">
                    <h1>WOLKITE POLYTECHNIC COLLEGE </h1>
                    <p>Excellence in Technical Education Since 1995</p>
                </div>
            </div>
        </div>
        
        <div class="progress-bar">
            <div class="step active">
                <div class="step-circle">1</div>
                <div class="step-label">Personal Info</div>
            </div>
            <div class="step">
                <div class="step-circle">2</div>
                <div class="step-label">Academic Details</div>
            </div>
            <div class="step">
                <div class="step-circle">3</div>
                <div class="step-label">Documents</div>
            </div>
            <div class="step">
                <div class="step-circle">4</div>
                <div class="step-label">Review & Submit</div>
            </div>
        </div>
        
        <div class="form-container">
            <form id="registrationForm" action="register.php" method="POST" enctype="multipart/form-data">
                <!-- Step 1: Personal Information -->
                <div class="form-step active" id="step1">
                    <h2><i class="fas fa-user-graduate"></i> Personal Information</h2>
                    
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label for="firstName" class="required">First Name</label>
                                <input type="text" id="firstName" name="firstName" placeholder="Enter your first name">
                                <div class="error" id="firstNameError">First name is required and must be at least 2 characters</div>
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label for="lastName" class="required">Last Name</label>
                                <input type="text" id="lastName" name="lastName" placeholder="Enter your last name">
                                <div class="error" id="lastNameError">Last name is required and must be at least 2 characters</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label for="email" class="required">Email Address</label>
                                <input type="email" id="email" name="email" placeholder="Enter your email">
                                <div class="error" id="emailError">Please enter a valid email address</div>
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label for="phone" class="required">Phone Number</label>
                                <input type="tel" id="phone" name="phone" placeholder="Enter your phone number">
                                <div class="error" id="phoneError">Please enter a valid phone number</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label for="dob" class="required">Date of Birth</label>
                                <input type="date" id="dob" name="dob">
                                <div class="error" id="dobError">Please enter your date of birth</div>
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label for="gender" class="required">Gender</label>
                                <select id="gender" name="gender">
                                    <option value="">Select Gender</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="other">Other</option>
                                </select>
                                <div class="error" id="genderError">Please select your gender</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="address" class="required">Permanent Address</label>
                        <textarea id="address" name="address" rows="3" placeholder="Enter your full permanent address"></textarea>
                        <div class="error" id="addressError">Address is required</div>
                    </div>

                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label for="nationality">Nationality</label>
                                <input type="text" id="nationality" name="nationality" placeholder="Your nationality">
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label for="idNumber">National ID Number</label>
                                <input type="text" id="idNumber" name="idNumber" placeholder="Your national ID">
                            </div>
                        </div>
                    </div>
                    
                    <div class="buttons">
                        <div></div>
                        <button type="button" class="btn btn-next" onclick="nextStep(1)">Next <i class="fas fa-arrow-right"></i></button>
                    </div>
                </div>
                
                <!-- Step 2: Academic Details -->
                <div class="form-step" id="step2">
                    <h2><i class="fas fa-book-open"></i> Academic Details</h2>
                    
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label for="department" class="required">Department</label>
                                <select id="department" name="department">
                                    <option value="">Select Department</option>
                                    <option value="computer-science">Computer Science & Engineering</option>
                                    <option value="civil">Civil Engineering</option>
                                    <option value="mechanical">Mechanical Engineering</option>
                                    <option value="electrical">Electrical Engineering</option>
                                    <option value="electronics">Electronics & Communication</option>
                                    <option value="automobile">Automobile Engineering</option>
                                </select>
                                <div class="error" id="departmentError">Please select a department</div>
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label for="program" class="required">Program</label>
                                <select id="program" name="program">
                                    <option value="">Select Program</option>
                                    <option value="diploma">Diploma</option>
                                    <option value="advanced-diploma">Advanced Diploma</option>
                                    <option value="btech">B.Tech</option>
                                </select>
                                <div class="error" id="programError">Please select program</div>
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label for="semester" class="required">Semester</label>
                                <select id="semester" name="semester">
                                    <option value="">Select Semester</option>
                                    <option value="1">First Semester</option>
                                    <option value="2">Second Semester</option>
                                    <option value="3">Third Semester</option>
                                    <option value="4">Fourth Semester</option>
                                    <option value="5">Fifth Semester</option>
                                    <option value="6">Sixth Semester</option>
                                </select>
                                <div class="error" id="semesterError">Please select semester</div>
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label for="academicYear" class="required">Academic Year</label>
                                <select id="academicYear" name="academicYear">
                                    <option value="">Select Year</option>
                                    <option value="2024">2024-2025</option>
                                    <option value="2023">2023-2024</option>
                                    <option value="2022">2022-2023</option>
                                </select>
                                <div class="error" id="academicYearError">Please select academic year</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label for="previousSchool" class="required">Previous School/College</label>
                                <input type="text" id="previousSchool" name="previousSchool" placeholder="Enter your previous institution">
                                <div class="error" id="previousSchoolError">Previous school/college is required</div>
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label for="qualification" class="required">Qualification</label>
                                <select id="qualification" name="qualification">
                                    <option value="">Select Qualification</option>
                                    <option value="high-school">High School</option>
                                    <option value="diploma">Diploma</option>
                                    <option value="bachelors">Bachelor's Degree</option>
                                    <option value="other">Other</option>
                                </select>
                                <div class="error" id="qualificationError">Please select qualification</div>
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label for="percentage" class="required">Percentage/GPA</label>
                                <input type="number" id="percentage" name="percentage" min="0" max="100" step="0.01" placeholder="Enter your percentage">
                                <div class="error" id="percentageError">Please enter a valid percentage between 0 and 100</div>
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label for="board">Board/University</label>
                                <input type="text" id="board" name="board" placeholder="Board/University name">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="achievements">Achievements & Extracurricular Activities</label>
                        <textarea id="achievements" name="achievements" rows="3" placeholder="List any academic achievements, awards, or extracurricular activities"></textarea>
                    </div>
                    
                    <div class="buttons">
                        <button type="button" class="btn btn-prev" onclick="prevStep(2)"><i class="fas fa-arrow-left"></i> Previous</button>
                        <button type="button" class="btn btn-next" onclick="nextStep(2)">Next <i class="fas fa-arrow-right"></i></button>
                    </div>
                </div>
                
                <!-- Step 3: Documents -->
                <div class="form-step" id="step3">
                    <h2><i class="fas fa-file-upload"></i> Document Upload</h2>
                    <p class="section-description">Please upload the required documents in PDF or image format (Max 2MB each)</p>
                    
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label for="photo" class="required">Passport Size Photo</label>
                                <input type="file" id="photo" name="photo" accept="image/*">
                                <div class="error" id="photoError">Passport photo is required</div>
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label for="idProof" class="required">ID Proof</label>
                                <input type="file" id="idProof" name="idProof" accept=".pdf,image/*">
                                <div class="error" id="idProofError">ID proof is required</div>
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label for="marksheet">Marksheet (Optional)</label>
                                <input type="file" id="marksheet" name="marksheet" accept=".pdf,image/*">
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label for="transferCertificate">Transfer Certificate</label>
                                <input type="file" id="transferCertificate" name="transferCertificate" accept=".pdf,image/*">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="additionalDocs">Additional Documents (Optional)</label>
                        <input type="file" id="additionalDocs" name="additionalDocs[]" multiple accept=".pdf,image/*">
                        <small>You can upload multiple files</small>
                    </div>
                    
                    <div class="buttons">
                        <button type="button" class="btn btn-prev" onclick="prevStep(3)"><i class="fas fa-arrow-left"></i> Previous</button>
                        <button type="button" class="btn btn-next" onclick="nextStep(3)">Next <i class="fas fa-arrow-right"></i></button>
                    </div>
                </div>
                
                <!-- Step 4: Review and Submit -->
                <div class="form-step" id="step4">
                    <h2><i class="fas fa-clipboard-check"></i> Review Your Information</h2>
                    
                    <div id="reviewContent">
                        <!-- Review content will be populated by JavaScript -->
                    </div>
                    
                    <div class="form-group" style="margin-top: 20px;">
                        <label>
                            <input type="checkbox" id="agreeTerms" name="agreeTerms">
                            I agree to the <a href="#" class="link">Terms and Conditions</a> and <a href="#" class="link">Privacy Policy</a> of Wolkite Polytechnic College
                        </label>
                        <div class="error" id="agreeTermsError">You must agree to the terms and conditions</div>
                    </div>

                    <div class="form-group">
                        <label>
                            <input type="checkbox" id="declareInfo" name="declareInfo">
                            I declare that all information provided is true and correct to the best of my knowledge
                        </label>
                        <div class="error" id="declareInfoError">You must declare the information is correct</div>
                    </div>
                    
                    <div class="buttons">
                        <button type="button" class="btn btn-prev" onclick="prevStep(4)"><i class="fas fa-arrow-left"></i> Previous</button>
                        <button type="submit" class="btn" id="submitBtn">Submit Registration <i class="fas fa-paper-plane"></i></button>
                    </div>
                </div>
            </form>
            
            <div class="form-footer">
                <p>Already have an account? <a href="admin/login.php" class="link">Admin Login</a> | <a href="#" class="link">Check Application Status</a></p>
                <p class="contact-info">
                    <i class="fas fa-phone"></i> +251 947125689 | 
                    <i class="fas fa-envelope"></i> admissions@wolkitepoly.edu
                </p>
            </div>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>