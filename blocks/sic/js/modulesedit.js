let table = document.getElementById("modulestbl");
let rows = table.tBodies[0].rows;
let form = document.forms.editmoduleform;
let select = form.moduleid;
let id = 0;
select.addEventListener("change", (e) => {
    id = parseInt(e.target.selectedOptions[0].value);
    let module = {
        id: id,
        codigo: "",
        startdate: 0,
        enddate: 0,
        sync: 0,
        async: 0
    };
    for(let e of rows){
        if(parseInt(e.children[0].innerText) == id) {
            let list = e.children
            module.codigo = list[1].innerText
            module.startdate = list[2].innerText
            module.enddate = list[3].innerText
            module.sync = parseInt(list[4].innerText)
            module.async = parseInt(list[5].innerText)
        }
    }

    form.codigo.value = module.codigo
    form.startdate.value = module.startdate
    form.enddate.value = module.enddate
    form.sincronas.value = module.sync
    form.asincronas.value = module.async
});