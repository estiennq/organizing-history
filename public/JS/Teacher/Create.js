
initialise();

function initialise(){

    let formElement = document.getElementById('createRoomForm');
    let roomNameInput = document.getElementById('roomName');
    let roomDescriptionInput = document.getElementById('roomDescription');

    formElement.addEventListener('submit', (event) => createRoom(event, roomNameInput.value, roomDescriptionInput.value));
}

async function createRoom(event, roomName, roomDescription){
    let data = {
        roomName: roomName,
        roomDescription: roomDescription
    };
    let response = await fetch('/teacher-create-room', {
        method: 'POST',
        body: JSON.stringify(data),
    });

    goToPath('/teacher');
}
