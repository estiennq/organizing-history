async function addLevel(levelId){
    let currentURL = window.location.href;
    let pageToLoad = currentURL + '/add';
    let data = {
        levelId: levelId,
    };
    let response = await fetch(pageToLoad, {
        method: 'POST',
        body: JSON.stringify(data),
    });

    goToPath(currentURL);
}