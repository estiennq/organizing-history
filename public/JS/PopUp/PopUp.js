

class PopUp{
    constructor(prompt, acceptFunction, acceptText, cancelFunction, cancelText, escapeFunction) {
        this.prompt = prompt;
        this.acceptFunction = acceptFunction;
        this.acceptText = acceptText;
        this.cancelFunction = cancelFunction;
        this.cancelText = cancelText;
        this.escapeFunction = escapeFunction;

        this.overlayElement = document.createElement('div');
        this.overlayElement.classList.add('overlay');
        document.body.append(this.overlayElement);
        this.overlayElement.addEventListener('click', this.escapeFunction);

        this.popupElement = document.createElement('div');
        this.popupElement.classList.add('popup');
        this.overlayElement.appendChild(this.popupElement);
        this.popupElement.addEventListener('click', stopPropagation);

        this.promptElement = document.createElement('p');
        this.promptElement.classList.add("popup-prompt");
        this.popupElement.appendChild(this.promptElement);
        this.promptElement.innerText = this.prompt;

        this.buttonsListElement = document.createElement('div');
        this.buttonsListElement.classList.add('buttonsList');
        this.popupElement.appendChild(this.buttonsListElement);
        this.acceptButtonElement = document.createElement('button');
        this.acceptButtonElement.classList.add('accept-button');
        this.popupElement.appendChild(this.acceptButtonElement);
        this.acceptButtonElement.innerText = this.acceptText;
        this.acceptButtonElement.addEventListener('click', this.acceptFunction);
        this.buttonsListElement.appendChild(this.acceptButtonElement);

        if(this.cancelText != null){
            this.cancelButtonElement = document.createElement('button');
            this.cancelButtonElement.classList.add('cancel-button');
            this.popupElement.appendChild(this.cancelButtonElement);
            this.cancelButtonElement.innerText = this.cancelText;
            this.cancelButtonElement.addEventListener('click', this.cancelFunction);
            this.buttonsListElement.appendChild(this.cancelButtonElement);
        }

        /*this.popup = document.getElementById("popup");
        this.popup.style.visibility="visible";
        this.popup.addEventListener('click', stopPropagation);

        this.overlay = document.getElementById('overlay');
        this.overlay.style.background = 'rgba(0, 0, 0, 0.5)';
        this.overlay.style.visibility="visible";

        this.overlay.addEventListener('click', cancelPopUp);

        this.question = document.getElementById('popupText');
        this.question.innerText = popupText;

        this.buttonCancel = document.querySelector(".popupButtonDenied");
        this.buttonCancel.addEventListener('click', cancelPopUp);

        this.buttonAccept = document.querySelector(".popupButtonAccepted");
        this.buttonAccept.addEventListener('click', validateFunction);*/
    }

    remove(){
        this.overlayElement.removeEventListener('click', this.escapeFunction);
        this.popupElement.removeEventListener('click', stopPropagation);
        this.acceptButtonElement.removeEventListener('click', this.acceptFunction);
        if(this.cancelButtonElement){
            this.cancelButtonElement.removeEventListener('click', this.cancelFunction);
        }
        this.overlayElement.remove();
    }
}

let popUp = null;
let userIdGlobal;

function cancelPopUp(){
    hidePopUp();
}

function showCustomPopUp(prompt, acceptFunction, acceptText, cancelFunction, cancelText, escapeFunction) {
    if(popUp != null) popUp.remove();
    popUp = new PopUp(prompt, acceptFunction, acceptText, cancelFunction, cancelText, escapeFunction);
}

function showPeopleConfirmationPopup(prompt, acceptFunction, peopleId) {
    userIdGlobal = peopleId;
    if(popUp != null) popUp.remove();
    popUp = new PopUp(prompt, acceptFunction, 'Oui', cancelPopUp, 'Non', cancelPopUp);
}

function hidePopUp() {
    /*let overlay = document.getElementById('overlay');
    overlay.style.background = 'rgba(0, 0, 0, 0)';
    overlay.style.visibility="hidden";
    let popup = document.getElementById("popup");
    popup.style.visibility="hidden";

    let buttonCancel = document.querySelector(".popupButtonDenied");
    buttonCancel.removeEventListener('click',(event) => this.cancelPopUp());*/
    //location.reload();

    popUp.remove();
}

/*function actionParDefaut() {
    // Vous pouvez laisser cette fonction vide ou ajouter un autre comportement par défaut
    alert("Action par défaut exécutée");
}*/

function togglePopupHeader(){
    let overlay = document.getElementById("overlayheader");
    overlay.style.visibility='visible';
    overlay.addEventListener('click', cancelPopupHeader);

    this.popupHeader = document.getElementById("popupHeader");
    this.popupHeader.addEventListener('click', stopPropagation);
    this.popupHeader.style.visibility="visible";
}

function cancelPopupHeader(){
    this.overlay = document.getElementById("overlayheader");
    this.overlay.style.visibility='hidden';

    let popupHeader = document.getElementById("popupHeader");
    popupHeader.style.visibility="hidden";
}

function stopPropagation(event) {
    event.stopPropagation();
}

// Teacher ------------------------------------------------------------

async function acceptTeacher(event) {
    event.preventDefault();
    event.stopPropagation();
    event.target.removeEventListener('click', this.validate);
    let data = {
        idUser: userIdGlobal,
    };
    let response = await fetch("/admin/user-accepted", {
        method: "POST",
        body: JSON.stringify(data),
    });
    hidePopUp();
    goToPath('/admin/teachers');
}
async function denyTeacher(event){
    event.preventDefault();
    event.stopPropagation();
    event.target.removeEventListener('click', this.validate);
    let data = {
        idUser: userIdGlobal,
    };
    let response = await fetch("/admin/user-denied", {
        method: "POST",
        body: JSON.stringify(data),
    })
    hidePopUp();
    goToPath('/admin/teachers');
}

// Student ------------------------------------------------------------

async function acceptStudent(event) {
    event.preventDefault();
    event.stopPropagation();
    event.target.removeEventListener('click', this.validate);
    let data = {
        idUser: userIdGlobal,
    };
    let response = await fetch("/admin/user-accepted", {
        method: "POST",
        body: JSON.stringify(data),
    });
    hidePopUp();
    goToPath('/admin/students');
}
async function denyStudent(event) {
    event.preventDefault();
    event.stopPropagation();
    event.target.removeEventListener('click', this.validate);
    let data = {
        idUser: userIdGlobal,
    };
    let response = await fetch("/admin/user-denied", {
        method: "POST",
        body: JSON.stringify(data),
    });
    hidePopUp();
    goToPath('/admin/students');
}

async function deleteRoom(event){
    event.preventDefault();
    event.stopPropagation();
    event.target.removeEventListener('click', this.validate);
    let data = {
        roomId: userIdGlobal,
    };
    let response = await fetch("/teacher-delete-room", {
        method: "POST",
        body: JSON.stringify(data),
    });
    hidePopUp();
    goToPath('/teacher');
}

async function deleteStudent(event){
    event.preventDefault();
    event.stopPropagation();
    let currentURL = window.location.href;
    event.target.removeEventListener('click', this.validate);
    let data = {
        studentId: userIdGlobal,
    };
    let response = await fetch(currentURL+'/remove-student', {
        method: "POST",
        body: JSON.stringify(data),
    });
    hidePopUp();
    goToPath(currentURL);
}