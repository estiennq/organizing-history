/**
 * Fonction pour naviguer entre les slicks
 */
function goToSlick(direction, pageAmount){
    let slickWidth = document.querySelector(".current-slick").offsetWidth;
    let currentSlick = document.querySelector(".current-slick");
    let currentIndex = currentSlick.dataset.slickIndex;

    let nextButton = document.querySelector(".next-button");
    let previousButton = document.querySelector(".previous-button");

    //Obtenir l'index de la slick à afficher
    let wantedIndex = Number(currentIndex) + direction;

    //Recherche de la slick à afficher dans les balises
    let foundSlick = null;
    for (const slick of currentSlick.parentNode.children) {
        if(!slick.classList.contains("slick")){
            continue;
        }
        if(Number(slick.dataset.slickIndex) === Number(wantedIndex)){
            foundSlick = slick;
            break;
        }
    }
    if(foundSlick === null){
        throw new TypeError("previous slick couldn't be found");
    }

    currentSlick.classList.remove("current-slick");
    foundSlick.classList.add("current-slick");

    //Déplacement de la slick
    foundSlick.parentNode.style.left = String(-(slickWidth * Number(wantedIndex))) + "px";

    let clickButton = new Audio('/Sounds/clickSlide.mp3');
    clickButton.volume=0.4;
    clickButton.play();

    //Changement de bouton
    if (wantedIndex === 0) {
        previousButton.setAttribute("hidden","");
    } else {
        previousButton.removeAttribute("hidden");
    }
    if (wantedIndex === pageAmount - 1) {
        nextButton.setAttribute("hidden","");
    } else {
        nextButton.removeAttribute("hidden");
    }
}