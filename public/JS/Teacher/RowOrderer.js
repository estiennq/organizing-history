class Row{

    static moveAnimationLength = 0.3;
    static deleteRowAnimationLength = 0.3;

    constructor(element) {
        this.element = element;
        this.moveUpButton = element.querySelector('.teacherTableLevelButtonUp');
        this.moveDownButton = element.querySelector('.teacherTableLevelButtonDown');
        this.deleteRowButton = element.querySelector('.teacherTableLevelButtonDeleteRow');

        this.moveUpButton.addEventListener('click', (event) => this.moveUp(event));
        this.moveDownButton.addEventListener('click', (event) => this.moveDown(event));
        this.deleteRowButton.addEventListener('click', (event) => this.deleteRow(event));

        this.updateButtons();
    }

    static getOtherRowObject(otherRowElement){
        return rows.find((row) => row.element === otherRowElement);
    }

    moveUp(event) {
        event.preventDefault();
        let siblingRow = this.element.previousElementSibling;
        let topValue = -this.element.offsetHeight;

        if (siblingRow === null) {
            return;
        }

        this.element.classList.add('moveUp');
        siblingRow.classList.add('moveDown');

        this.setDisableButtons(true);
        Row.getOtherRowObject(siblingRow).setDisableButtons(true);

        setTimeout(() => {
            this.switchRowWithSibling(siblingRow, topValue);
            this.element.classList.remove('moveUp');
            siblingRow.classList.remove('moveDown');

        }, Row.moveAnimationLength * 1000);
        this.moveUpInDB();
    }
    async moveUpInDB(){
        let pageToLoad = window.location.href + '/move-up-level';
        let data = {
            levelId: this.element.dataset.levelId,
        };
        let response = await fetch(pageToLoad, {
            method: 'POST',
            body: JSON.stringify(data),
        });
        console.log(data);

    }
    moveDown(event){
        event.preventDefault();
        let siblingRow = this.element.nextElementSibling;
        let topValue = siblingRow ? siblingRow.offsetHeight : 0;

        if (siblingRow !== null) {
            this.element.classList.add('moveDown');
            siblingRow.classList.add('moveUp');

            this.setDisableButtons(true);
            Row.getOtherRowObject(siblingRow).setDisableButtons(true);

            setTimeout(() => {
                this.switchRowWithSibling(siblingRow, topValue);
                this.element.classList.remove('moveDown');
                siblingRow.classList.remove('moveUp');

            }, Row.moveAnimationLength * 1000);
        }
        this.moveDownInDB();
    }
    async moveDownInDB(){
        let pageToLoad = window.location.href + '/move-down-level';
        let data = {
            levelId: this.element.dataset.levelId,
        };
        let response = await fetch(pageToLoad, {
            method: 'POST',
            body: JSON.stringify(data),
        });
        console.log(data);
        console.log(pageToLoad)
    }

    deleteRow(){
        this.element.classList.add('delete');

        setTimeout(() => {
            let previousRow = this.element.previousElementSibling;
            let nextRow = this.element.nextElementSibling;

            this.element.remove();
            //this.updateButtons();
            if(previousRow !== null){
                Row.getOtherRowObject(previousRow).updateButtons();
            }
            if(nextRow !== null){
                Row.getOtherRowObject(nextRow).updateButtons();
            }

        }, Row.deleteRowAnimationLength * 1000);
        this.deleteInDB();
    }
    async deleteInDB(){
        let pageToLoad = window.location.href + '/remove-level';
        let data = {
            levelId: this.element.dataset.levelId,
        };
        let response = await fetch(pageToLoad, {
            method: 'POST',
            body: JSON.stringify(data),
        });
    }

    switchRowWithSibling(siblingRow, topValue){
        if (topValue < 0) {
            this.element.parentNode.insertBefore(this.element, siblingRow);
        } else {
            this.element.parentNode.insertBefore(this.element, siblingRow.nextSibling);
        }

        this.updateButtons();
        Row.getOtherRowObject(siblingRow).updateButtons();
    }

    setDisableButtons(isDisable){
        this.moveUpButton.disabled = isDisable;
        this.moveDownButton.disabled = isDisable;
    }

    updateButtons(){
        let parent = this.element.parentElement;

        this.moveUpButton.disabled = false;
        this.moveDownButton.disabled = false;

        if (this.element === parent.firstElementChild) {
            this.moveUpButton.disabled = true;
        }
        if (this.element === parent.lastElementChild) {
            this.moveDownButton.disabled = true;
        }
    }
}


let rows = [];

initialise();
function initialise() {
    document.querySelectorAll(".teacherRoomLevelRow").forEach((element) => rows.push(new Row(element)) );
}