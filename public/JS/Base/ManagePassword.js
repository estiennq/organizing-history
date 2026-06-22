initialise()
function initialise() {
    //Récupération des éléments input générés par doctrine
    passwordInput = document.getElementById('registration_form_plainPassword') || document.getElementById('student_registration_form_plainPassword');

    passwordInput.addEventListener('input', modifiyCheckbox);


    function modifiyCheckbox(event) {
        let passwordValue = passwordInput.value;
        let minNumberCondition = document.getElementById('minNumberCondition');
        let lowCaseCondition = document.getElementById('lowCaseCondition');
        let upCaseCondition = document.getElementById('upCaseCondition');
        let numberCondition = document.getElementById('numberCondition');
        let speCaractCondition = document.getElementById('speCaractCondition');

        if(passwordValue.length >= 12){
            minNumberCondition.checked = true;
        }else{
            minNumberCondition.checked = false;
        }

        if (verify(/[A-Z]/,passwordValue)){
            lowCaseCondition.checked = true;
        }else{
            lowCaseCondition.checked = false;
        }

        if (verify(/[a-z]/,passwordValue)){
            upCaseCondition.checked = true;
        }else{
            upCaseCondition.checked = false;
        }

        if (verify(/\d/,passwordValue)){
            numberCondition.checked = true;
        }else{
            numberCondition.checked = false;
        }

        if (verify(/[!@#$%^&*(),.?":{}|<>]/,passwordValue)){
            speCaractCondition.checked = true;
        }else{
            speCaractCondition.checked = false;
        }

    }
}

function verify(chain, chainToVerify){
    return chain.test(chainToVerify);
}

function verifiyPassword(){
    let passwordValue = passwordInput.value;
    let confirmationPassword = document.getElementById('confirmationPassword');

    let minNumberCondition = document.getElementById('minNumberCondition');
    let lowCaseCondition = document.getElementById('lowCaseCondition');
    let upCaseCondition = document.getElementById('upCaseCondition');
    let numberCondition = document.getElementById('numberCondition');
    let speCaractCondition = document.getElementById('speCaractCondition');

    let errorMessage = document.getElementById('errorMessage');
    errorMessage.style.display = "none";
    if(passwordValue.length >= 12 &&
        verify(/[A-Z]/,passwordValue) &&
        verify(/[a-z]/,passwordValue) &&
        verify(/\d/,passwordValue) &&
        verify(/[!@#$%^&*(),.?":{}|<>]/,passwordValue))
    {
        if (passwordValue === confirmationPassword.value){
            return true;
        }else{
            errorMessage.textContent  = 'Les deux mots de passe ne sont pas identiques !';
            errorMessage.style.display = "block";
            return false;
        }
    }else{
        errorMessage.textContent  = 'Le mot de passe ne remplit pas les caractéristiques minimums';
        errorMessage.style.display = "block";
        return false;
    }
}