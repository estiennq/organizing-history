
function subToPath(pathToRemove) {
    // Get the current URL
    let currentURL = window.location.href;

    // Remove the specified substring
    let subPath = currentURL.replace(pathToRemove, '');

    // Redirect to the modified URL
    window.location.href = subPath;
}

function addToPath(path){
    window.location.href += path;
}

function goToPath(path){
    window.location.href = path;
}