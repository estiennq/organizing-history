function togglePasswordVisibility(elementClass) {
    passwordInput = document.getElementById('registration_form_plainPassword')
        || document.getElementById('student_registration_form_plainPassword')
        || document.getElementById('inputPassword');
    let passwordVisibilityVisible = document.querySelector(".toggle-passwordVisibilityVisible");
    let passwordVisibilityHidden = document.querySelector(".toggle-passwordVisibilityHidden");

    if (passwordInput.type === "password") {
        passwordInput.type = "text";
        passwordVisibilityVisible.style.display = "block";
        passwordVisibilityHidden.style.display = "none";
    } else {
        passwordInput.type = "password";
        passwordVisibilityVisible.style.display = "none";
        passwordVisibilityHidden.style.display = "block";
    }
}