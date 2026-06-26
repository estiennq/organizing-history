
initialise();

function initialise(){
    document.querySelector('.validityCheckButton').addEventListener('click', requestLevelValidityCheck);
}

async function requestLevelValidityCheck(){

    hideCorrection();

    // Récupère la réponse du joueur
    let playerAnswer = getPlayerAnswer()

    // Demande au serveur si la réponse est juste
    let data = JSON.stringify({'answer':playerAnswer});
    //console.log(data);
    let response = await fetch(window.location.pathname + '/solution', {
        method: "POST",
        body: data
    });
    let result = await response.json();

    let succeedAudio = new Audio('/Sounds/correct.mp3');
    let failAudio = new Audio('/Sounds/incorrect.mp3');

    //console.log(result);

    // Indique au jetons à quelle action de la solution ils correspondent.
    setActionIdsToTokens(result['actionIds']);

    if(result['isValid'] === 'false'){
        // Indique au serveur que l'élève à fait une erreur (ne marche pas directement dans le controller pour une raison inconnue)
        let error = JSON.stringify({'error':'true'});
        let response = await fetch(window.location.pathname + '/solution/manage-error', {
            method: "POST",
            body: error
        });
        showCorrection(result['firstInvalidBox']);

        playSound(failAudio);
        // BLoque le bouton de vérification puor éviter le spam
        lockValidityCheck(10);
    }
    else{
        // Indique au serveur que l'élève à fait une erreur (ne marche pas directement dans le controller pour une raison inconnue)
        let error = JSON.stringify({'error':'false'});
        let response = await fetch(window.location.pathname + '/solution/manage-error', {
            method: "POST",
            body: error
        });
        hideCorrection();

        playSound(succeedAudio);

        showCustomPopUp("Félicitations, vous avez réussi le niveau !\n Vous pouvez maintenant avoir plus d'informations sur les grandes étapes de cet évènement.",
            activatePostCompletionMode, "Ok",
            null, null, cancelPopUp);
    }
}
// Indique au jetons à quelle action de la solution ils correspondent.
function setActionIdsToTokens(actionIds){
    let actionElements = document.querySelectorAll('.action:not(.resource)');
    for (let i = 0; i < actionElements.length; i++) {
        let tokenObject = actionTokens.find((token) => token.element === actionElements[i]);
        tokenObject.actionId = actionIds[i];
    }
}
async function activatePostCompletionMode(event){
    event.preventDefault();
    event.stopPropagation();
    event.target.removeEventListener('click', this.validate);

    hidePopUp();
    isDragAndDropEnabled = false;

    let gameBottom = document.querySelector('.game-bottom');

    // Supprime la bar de resource et le boutton vérifier
    let childCount = gameBottom.childElementCount;
    for (let i = 0; i < childCount; i++) {
        gameBottom.lastElementChild.remove();
    }

    // Demande au serveur si il y a une difficulté suivante
    let response = await fetch(window.location.pathname + '/has-next');
    let result = await response.text();

    // Ajoute les boutons pour revenir à la liste des niveaux et pour passer à la difficulté suivante si il y en a une.
    let goBackToLevelsButton = document.createElement('button');
    goBackToLevelsButton.classList.add('go-back-button');
    gameBottom.appendChild(goBackToLevelsButton);
    // Notre glossaire à eu des changements de dernière minutes. Il y a donc des différences entre le nommage dans le code et dans l'interface
    goBackToLevelsButton.innerText = "Revenir à la liste des chapitres";
    goBackToLevelsButton.addEventListener('click', goBackToLevels);

    if(result === 'true'){
        // Ajoute un bouton pour aller au niveau suivant si il y en a un
        let goToNextLevelButton = document.createElement('button');
        goToNextLevelButton.classList.add('go-next-button');
        gameBottom.appendChild(goToNextLevelButton);
        goToNextLevelButton.innerText = "Niveau suivant";
        goToNextLevelButton.addEventListener('click', goNextDifficulty);
    }

    // Active les bulles d'info sur les actions
    for(let action in actionTokens){
        actionTokens[action].enableContext();
    }
}
function goBackToLevels(event) {
    event.preventDefault();
    event.stopPropagation();
    event.target.removeEventListener('click', this.validate);

    let isDemoMode = document.querySelector('.game').dataset.demoMode === 'true';
    goToPath(isDemoMode ? '/demo' : '/student');
}
function goNextDifficulty(event) {
    event.preventDefault();
    event.stopPropagation();
    event.target.removeEventListener('click', this.validate);

    goToPath(window.location.pathname + '/next');
}
async function lockValidityCheck(seconds){
    let button = document.querySelector('.validityCheckButton');
    let originalText = button.innerText;

    button.disabled = true;

    let timer = seconds;
    button.innerText = parseInt(timer, 10);
    // Décrémente le timer sur l'affichage toutes les secondes
    let intervalId = setInterval(() => {
        timer--;
        button.innerText = parseInt(timer, 10);
    }, 1000);

    // Attends que le timer soit fini
    await new Promise(r => setTimeout(r, seconds * 1000));

    // Désactive l'interval pour pas qu'il s'exécute pour toujours
    clearInterval(intervalId);

    button.disabled = false;
    button.innerText = originalText;
}

function getPlayerAnswer(){
    let answer = [];

    // Itération sur toutes les cases jusqu'à la fin ou qu'une des case soit vide
    let i = 0;
    while(i < boxes.length && boxes[i].element.firstElementChild !== null){

        let currentActionData = getActionData(boxes[i].element.firstElementChild);
        answer[i] = currentActionData;
        i++;
    }
    return answer;
}

function getActionData(actionElement){

    let actionData = {};

    actionData['SceneId'] = actionElement.dataset.tokenId;
    actionData['CharacterContainers'] = [];

    let actionCharacterContainers = actionElement.querySelectorAll('.characterContainer');

    actionCharacterContainers.forEach(
        (item, index) => {
            let currentContainerData = getCharacterContainerData(item);
            actionData['CharacterContainers'][index] = currentContainerData;
        }
    )

    return actionData;
}

function getCharacterContainerData(characterContainerElement) {
    let containerData = {};

    containerData['containerId'] = characterContainerElement.dataset.containerId;

    if(characterContainerElement.firstElementChild !== null){
        containerData['characterId'] = characterContainerElement.firstElementChild.dataset.tokenId;
    }
    else{     // Traitement du cas particulier où le character container ne contient pas de personnage => id de -1
        containerData['characterId'] = '-1';
    }

    return containerData;
}

function showCorrection(firstInvalidBoxId){

    for(let i = 0; i < firstInvalidBoxId + 1; i++){
        boxes[i].hideCorrection();
        boxes[i].setCorrection(i !== firstInvalidBoxId);
    }
}
function hideCorrection(){
    for(let box in boxes){
        boxes[box].hideCorrection();
    }
}
function playSound(soundName){
    soundName.volume=globalVolume;
    soundName.play();
}