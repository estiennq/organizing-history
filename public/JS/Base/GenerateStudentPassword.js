initialise();
function initialise() {
    //Récupération des éléments input générés par doctrine
    let lastNameInput = document.getElementById('student_registration_form_lastName');
    let firstNameInput = document.getElementById('student_registration_form_firstName');

    let passwordInput = document.getElementById('student_registration_form_plainPassword');
    passwordInput.type = "text";

    lastNameInput.addEventListener('input', generatePassword);
    firstNameInput.addEventListener('input', generatePassword);

    function generatePassword(event) {
        let lastName = lastNameInput.value;
        let firstName = firstNameInput.value;

        // Mettre à jour le champ de login
        passwordInput.value = lastName.toLowerCase() + '.' + firstName.toLowerCase();
    }
}