let form = document.forms.statechangeform;
let selects = {};
let nodes = form.getElementsByTagName("select");

for (let node of nodes) {
    selects[node.name] = parseInt(node.value);
}

function resetStates() {
    for (let node of nodes) {
        node.value = selects[node.name];
    }
}

let btn1 = document.getElementById("resetstatesmodal");
let btn2 = document.getElementById("cancelstatesmodal");

btn1.addEventListener("click", (e) => {
    resetStates();
})
btn2.addEventListener("click", (e) => {
    resetStates();
})
