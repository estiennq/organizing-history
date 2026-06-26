
// Définition des classes ---------------------------
// --------------------------------------------------------------------------------- TokenContainer
class TokenContainer{
    constructor(element) {
        this.element = element;
        this.id = this.element.dataset.actionId;
        this.parentElement = element.parentElement;

        //Lie les évenements de l'élément à la classe. Syntaxe "() => foo()" permet de rester dans le context de l'objet.
        this.element.addEventListener('dragover', (event) => this.dragOver(event));
        this.element.addEventListener('drop', (event) => this.drop(event));
    }
    // Override par les classes filles. Dépose le token si il est valide. Retourne bool du succes.
    dropToken(token){}

    // Override par les classes filles. Interverti token avec un des parents. Retourne bool du succes.
    swapToken(token, closestParentBox, closestParentCharacterContainer){}

    // Interverti token1 et token2.
    swapElementWith(token1, token2){
        let token2Parent = token2.parentElement;

        if(token1.parentElement != null){   //Dans le cas où le token vient d'être cloné il n'a pas de parent et token2 est supprimé
            token1.parentElement.appendChild(token2);
        }
        else{
            this.deleteToken(token2);
        }
        token2Parent.appendChild(token1);

        this.getTokenObject(token1).updateSprite();
        this.getTokenObject(token2).updateSprite();
    }

    // Récupère l'objet à partir de l'élement
    getTokenObject(tokenElement){
        let foundToken = actionTokens.find((token) => token.element === tokenElement);
        if(foundToken === undefined){
            foundToken = characterTokens.find((token) => token.element === tokenElement);
        }
        return foundToken;
    }

    deleteToken(token){
        let tokenObject = this.getTokenObject(token);
        if(token.classList.contains('action')){
            actionTokens.slice(actionTokens.indexOf(tokenObject), 1);
        }
        else if(token.classList.contains('character')){
            characterTokens.slice(characterTokens.indexOf(tokenObject), 1);
        }
        else{
            throw Error('Unexpected token');
        }
        token.remove();
    }

    // Indique si le container est prêt à recevoir ce token.
    dragOver(event) {
        if(!isDragAndDropEnabled){
            return;
        }
        event.preventDefault();
    }

    // Point d'entrée pour drop ou swap.
    drop(event) {
        if(!isDragAndDropEnabled){
            return;
        }
        event.preventDefault();
        let data = event.dataTransfer.getData("text/plain");
        let draggedElementOrigin = document.getElementById(data);
        if (!draggedElementOrigin) return;
        let draggedElement = draggedElementOrigin.classList.contains("resource") ? this.cloneToken(draggedElementOrigin) : draggedElementOrigin;
        let closestParent = this.element.closest(".characterContainer, .box");
        let closestParentBox = this.element.closest(".box");
        let closestParentCharacterContainer = this.element.closest(".characterContainer");
        let draggedElementParentAction = draggedElement.closest(".action");
        let dropAudio = new Audio('/Sounds/drop.mp3');

        if(closestParent.childElementCount === 0){  //Drop normal

            let wasSuccessful = this.dropToken(draggedElement);

            // Update le sprite du parent
            if(draggedElementParentAction != null){
                this.getTokenObject(draggedElementParentAction).updateSprite();
            }

            if(wasSuccessful){
                this.getTokenObject(draggedElement).updateSprite();
                this.playSound(dropAudio);
                hideCorrection()
                //Empêche l'appel de l'event sur le parent
                event.stopPropagation();
            }
        }
        else{  //Swap des éléments (pas avec une resource)
            let wasSuccessful = this.swapToken(draggedElement, closestParentBox, closestParentCharacterContainer);

            if(wasSuccessful){
                this.playSound(dropAudio)
                hideCorrection()
                //Empêche l'appel de l'event sur le parent
                event.stopPropagation();
            }
        }
    }

    // Fait un double de token et retourne l'élement.
    cloneToken(token){
        let clonedToken = token.cloneNode(true);
        clonedToken.id = clonedToken.id + "-" + String(instanceCounter++);
        clonedToken.classList.remove("resource");

        clonedToken.querySelector('span').remove();

        if(clonedToken.classList.contains('action')){
            actionTokens.push( new ActionToken(clonedToken) );

            let characterContainerElements = clonedToken.querySelectorAll('.characterContainer');
            for (let i = 0; i < characterContainerElements.length; i++) {
                characterContainers.push( new CharacterContainer(characterContainerElements[i]) );
            }
        }
        else if(clonedToken.classList.contains('character')){
            characterTokens.push( new CharacterToken(clonedToken) );
        }
        else{
            throw new Error('Unexpected token');
        }
        return clonedToken;
    }

    playSound(sound){
        sound.volume = globalVolume;
        sound.play();
        console.log("containerSound " + sound.src);
    }
}
// ---------------------------------------------------------------------------------
class Box extends TokenContainer{
    constructor(element) {
        super(element);
    }
    dropToken(token){
        if(!token.classList.contains("action") || !this.element.classList.contains("box")){
            return false;
        }
        this.element.appendChild(token);
        //checkCollisionAndRemoveBorder(this.element);
        return true;
    }
    swapToken(token, closestParentBox, closestParentCharacterContainer){
        if(!token.classList.contains("action") || closestParentBox.childElementCount === 0){
            return false;
        }
        this.swapElementWith(token, closestParentBox.firstElementChild);
        return true;
    }

    setCorrection(isValid){
        if(isValid){
            this.element.classList.add('valid');
        }
        else{
            this.element.classList.add('invalid');
        }
    }
    hideCorrection(){
        this.element.classList.remove('valid');
        this.element.classList.remove('invalid');
    }
}
class CharacterContainer extends TokenContainer{
    constructor(element) {
        super(element);

        this.element.addEventListener('dragleave', (event) => this.dragLeave(event));
        this.element.addEventListener('dragend', (event) => this.dragEnd(event));
    }
    drop(event){
        this.element.classList.remove('hover');
        super.drop(event);
    }
    dropToken(token){
        if(!token.classList.contains("character") || !this.element.classList.contains("characterContainer")
            || this.element.parentElement.classList.contains("resource")){
            return false;
        }
        this.element.appendChild(token);
        //checkCollisionAndRemoveBorder(this.element);
        return true;
    }
    swapToken(token, closestParentBox, closestParentCharacterContainer){
        if(!token.classList.contains("character") || closestParentCharacterContainer.childElementCount === 0){
            return false;
        }
        this.swapElementWith(token, closestParentCharacterContainer.firstElementChild);
        return true;
    }
    dragOver(event) {
        if(!isDragAndDropEnabled){
            return;
        }
        super.dragOver(event);
        this.element.classList.add('hover');
    }
    dragLeave(event){
        if(!isDragAndDropEnabled){
            return;
        }
        event.preventDefault();
        this.element.classList.remove('hover');
    }
    dragEnd(event){
        if(!isDragAndDropEnabled){
            return;
        }
        event.preventDefault();
        this.element.classList.remove('hover');
    }
}
// ---------------------------------------------------------------------------------
class ResourceBar extends TokenContainer{
    constructor(element) {
        super(element)
    }

    // Supprime le token si il vient d'un container.
    drop(event){
        if(!isDragAndDropEnabled){
            return;
        }
        event.preventDefault();
        let data = event.dataTransfer.getData("text/plain");
        let draggedElement = document.getElementById(data);
        if (!draggedElement) return;
        let draggedElementParentAction = draggedElement.closest(".action");
        let dropResourceBarAudio = new Audio('/Sounds/deleteToken.mp3');
        dropResourceBarAudio.volume = 1;

        if(!draggedElement.classList.contains('resource')){
            this.deleteToken(draggedElement);
            this.playSound(dropResourceBarAudio);
            this.getTokenObject(draggedElementParentAction).updateSprite();
        }
    }
}
// ---------------------------------------------------------------------------------
class NavigationArrow{
    constructor(element) {
        this.element = element;
        this.timeOutId = null;

        this.element.addEventListener('dragenter', (event) => this.dragEnter(event));
        this.element.addEventListener('dragleave', (event) =>  this.dragLeave(event));
    }

    dragEnter(event){
        event.preventDefault();
        event.stopPropagation();
        this.timeOutId = setTimeout(() => {
            clearTimeout(this.timeOutId);
            this.element.click();
        }, 0.3 * 1000);
    }
    dragLeave(event){
        event.preventDefault();
        event.stopPropagation();
        if(this.timeOutId !== null){
            clearTimeout(this.timeOutId);
        }
    }
}
// --------------------------------------------------------------------------------- Token
class Token{
    constructor(element) {
        this.element = element;
        this.id = this.element.dataset.actionId;
        this.parentElement = element.parentElement;

        //Lie les évenements de l'élément à la classe. Syntaxe () => foo() permet de rester dans le context de l'objet.
        this.element.addEventListener('dragstart', (event) => this.dragStart(event))
        //this.element.addEventListener('dragover', this.allowDrop);
        //this.element.addEventListener('drop', this.drop);
    }

    // Récupère l'objet à partir de l'élement
    getContainerObject(tokenElement){
        let foundToken = boxes.find((token) => token.element === tokenElement);
        if(foundToken === undefined){
            foundToken = characterContainers.find((token) => token.element === tokenElement);
        }
        return foundToken;
    }

    dragStart(event) {
        if(!isDragAndDropEnabled){
            return;
        }
        event.stopPropagation();
        event.dataTransfer.setData("text/plain", this.element.id);

        let dragAudio= new Audio('/Sounds/drag.mp3');
        this.playSound(dragAudio);
    }

    setSprite(spriteId){}

    updateSprite() {}

    playSound(sound){
        sound.volume = globalVolume;
        sound.play();
        console.log("tokenSound " + sound.src);
    }
}
// ---------------------------------------------------------------------------------
class ActionToken extends Token{
    constructor(element) {
        super(element);
        this.characterContainer = this.element.querySelectorAll('.characterContainer');
    }

    updateSprite() {

        if(!this.element.classList.contains("action")){
            throw new Error("ActionToken should include css class 'action'")
        }

        this.parentElement = this.element.parentElement;

        if (this.parentElement != null && this.parentElement.classList.contains("resourceContainer")) {
            this.setSprite("resource");
        }
        else if (this.parentElement != null && this.parentElement.classList.contains("box")){
            if (this.element.hasChildNodes()) {

                let isFilled = true;

                this.element.querySelectorAll(".characterContainer").forEach((container) => {
                    if (!container.hasChildNodes()){
                        isFilled = false;
                    }
                });

                if (isFilled) {
                    this.setSprite("active");
                } else {
                    this.setSprite("inactive");
                }
            }
        }
    }

    setSprite(spriteId) {
        this.element.style.backgroundImage =
            "url(/img/Scenes/" + this.element.dataset.tokenSprite + "/" + this.element.dataset.tokenSprite + "-" + spriteId + ".png)";
    }

    async enableContext() {

        if(!this.actionId){
            return;
        }

        this.contextIconContainerElement = document.createElement('div');
        this.contextIconContainerElement.classList.add("icon-container");
        this.element.append(this.contextIconContainerElement);
        this.contextIconElement = document.createElement('span');
        this.contextIconElement.classList.add('action-context-icon');
        this.contextIconContainerElement.append(this.contextIconElement);
        this.contextIconElement.innerText = "i"
        // Demande au serveur le context de l'action
        let data = JSON.stringify({'actionId':this.actionId});

        let response = await fetch(window.location.pathname + '/action-context', {
            method: "POST",
            body: data
        });
        let result = await response.text();

        this.contextIconElement.setAttribute('title', result);

        /*this.contextElement = document.createElement('span');
        this.contextElement.classList.add('');*/
    }
}

// ---------------------------------------------------------------------------------
class CharacterToken extends Token {
    constructor(element) {
        super(element);
    }

    updateSprite() {
        if (!this.element.classList.contains("character")) {
            throw new Error("CharacterToken should include css class 'CharacterToken'")
        }

        this.parentElement = this.element.parentElement;

        if(this.parentElement != null && this.parentElement.classList.contains("resourceContainer")){
            this.setSprite("resource");
        }
        else if(this.parentElement != null && this.parentElement.classList.contains("characterContainer")){
            this.setSprite(this.parentElement.dataset.containerName);
            let characterContainer = this.getContainerObject(this.parentElement);
            let parentAction = characterContainer.getTokenObject(characterContainer.parentElement);
            this.element.style.left = this.parentElement.dataset.offsetX + '%';
            this.element.style.top = this.parentElement.dataset.offsetY + '%';

            parentAction.updateSprite();
        }
    }

    setSprite(spriteId) {
        this.element.querySelector('img').src =
            "/img/Characters/" + this.element.dataset.tokenSprite + "/" + this.element.dataset.tokenSprite + "-" + spriteId + ".png";
    }
}

// Initialisation ---------------------------------------------------------------------------------


let isDragAndDropEnabled = true;
let instanceCounter = 0;
let actionTokens = [];
let characterTokens = [];
let boxes = [];
let characterContainers = [];
let resourceBar;
let navigationArrows = [];
let globalVolume = 0.2;
initialiseElements();

function initialiseElements(){
    let actionElements = document.querySelectorAll('.action');
    for (let i = 0; i < actionElements.length; i++) {
        actionTokens.push( new ActionToken(actionElements[i]) );
        actionTokens[i].updateSprite();
    }

    let characterElements = document.querySelectorAll('.character');
    for (let i = 0; i < characterElements.length; i++) {
        characterTokens.push( new CharacterToken(characterElements[i]) );
        characterTokens[i].updateSprite();
    }

    let boxElements = document.querySelectorAll('.box');
    for (let i = 0; i < boxElements.length; i++) {
        boxes.push( new Box(boxElements[i]) );
    }

    let characterContainerElements = document.querySelectorAll('.characterContainer');
    for (let i = 0; i < characterContainerElements.length; i++) {
        characterContainers.push( new CharacterContainer(characterContainerElements[i]) );
    }

    resourceBar = new ResourceBar(document.querySelector(".resourceContainer"));

    let navigationArrowElements = document.querySelectorAll('.slick-navigation');
    for (let i = 0; i < navigationArrowElements.length; i++) {
        navigationArrows.push( new NavigationArrow(navigationArrowElements[i]) );
    }
}