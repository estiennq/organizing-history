initialise();
function initialise() {
    //Récupération des éléments input générés par doctrine
    let lastNameInput = document.getElementById('registration_form_lastName') || document.getElementById('student_registration_form_lastName');
    let firstNameInput = document.getElementById('registration_form_firstName') || document.getElementById('student_registration_form_firstName');

    let loginInput = document.getElementById('login');

    lastNameInput.addEventListener('input', generateLogin);
    firstNameInput.addEventListener('input', generateLogin);

    function generateLogin(event) {
        let lastName = lastNameInput.value;
        let firstName = firstNameInput.value;

        // Mettre à jour le champ de login
        loginInput.value = lastName.slice(0, 7).toLowerCase() + firstName.slice(0, 1).toLowerCase();
    }
}