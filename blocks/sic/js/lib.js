import { App } from './app.js'

String.prototype.toHHMMSS = function () {
    var sec_num = parseInt(this, 10)
    var hours   = Math.floor(sec_num / 3600)
    var minutes = Math.floor((sec_num - (hours * 3600)) / 60)
    var seconds = sec_num - (hours * 3600) - (minutes * 60)
    if (hours   < 10) {hours   = "0"+hours;}
    if (minutes < 10) {minutes = "0"+minutes;}
    if (seconds < 10) {seconds = "0"+seconds;}
    return hours + ' horas ' + minutes + ' minutos ' + seconds + " segundos";
}

const app = new App("#history_view")
app.start()


function renderRes(data, parent){
    for(let registro in data){
        let object = data[registro]
        console.log("registro", registro, "=>", object)
        for(let key in object){
            let subobject = object[key]
            if(subobject == null){
                let box = document.createElement("h5")
                box.innerText = key + ": " + subobject
                parent.appendChild(box)
                continue
            }else if(typeof subobject === 'object'){
                let subparent = document.createElement("div")
                subparent.setAttribute("style", "padding-left:1vw;margin-left:2vw;")
                let box = document.createElement("h5")
                box.innerText = key + ": ["
                parent.appendChild(box)
                renderResponse(subobject, subparent)
                parent.appendChild(subparent)
                let box2 = document.createElement("h5")
                box2.innerText = "]"
                parent.appendChild(box2)
            }else{
                let box = document.createElement("h5")
                box.innerText = key + ": " + subobject
                parent.appendChild(box)
            }
        }
    }
}


function renderEnvio(envios, parent, id){
    let p = document.createElement("h5")
    p.innerText = "Registro de envios:"
    parent.appendChild(p)
    let box = document.createElement("div")
    box.classList.add("p-2")
    box.classList.add("card")
    let headers = ["Alumno", "Conectividad", "Avance"]
    let table = new Table("envio_"+id)
    if(Array.isArray(envios)){
        
        for(let registro of envios){
            let node = document.createElement("p")
            node.innerText = "Codigo: " + registro.codigo + " Mensaje: " + registro.mensaje
            box.appendChild(node)
        }
    }else{
        console.error("Envios no es array", envios)
    }
    parent.appendChild(box)
}

function renderErrores(errores, parent, id){
    let p = document.createElement("h5")
    p.innerText = "Registro de errores:"
    parent.appendChild(p)
    let box = document.createElement("div")
    box.classList.add("p-2")
    let headers = ["Alumno", "Conectividad", "Avance"]
    if(Array.isArray(errores)){
        let table = new Table("errores_"+id)
        for(let registro of errores){
            let node = document.createElement("p")
            node.innerText = "Codigo: " + registro.codigo + " Mensaje: " + registro.mensaje + "alumno:" + registro.alumno
            box.appendChild(node)
        }
    }else{
        console.error("Errores no es array", errores)
    }

    parent.appendChild(box)
}
function renderResponse(child, parent){
    if(typeof child !== 'object') return renderContent(JSON.parse(child), parent)

    if(Array.isArray(child)){
        for(let key in child){
            let box = document.createElement("div")
            box.classList.add("card")
            box.classList.add("my-2")
            box.classList.add("p-2")
            let object = JSON.parse(child[key])
            console.log("Value:", object)
            for(let prop in object){
                if(!isNaN(parseInt(prop))){
                    let c = document.createElement("h4")
                    c.innerText = "ID Curso: " + prop
                    box.appendChild(c)
                    break
                }
            }
            if(object.hasOwnProperty('response')){
                let response = JSON.parse(object.response)
                console.log("Response:", response)
                let node = document.createElement("p")
                node.innerText = "ID Proceso: " + response.id_proceso + " | Respuesta SIC: " 
                    + (response.respuesta_SIC == null ? "No hay comentarios" : response.respuesta_SIC)
                box.appendChild(node)
                response.hasOwnProperty('envio') ? renderEnvio(response.envio, box, response.id_proceso) : renderEnvio(response.datosEnviados, box, response.id_proceso)
                response.hasOwnProperty('errores') ? renderErrores(response.errores, box, response.id_proceso) : renderErrores(response.datosError, box, response.id_proceso)
            }
            parent.appendChild(box)
        }
    } 
}

// let container = document.querySelector("#history_view")
// container.classList.add("card")
// container.classList.add("p-2")
// container.style = "border: none"
// let json = JSON.parse(container.getAttribute("errors"))
// renderResponse(json, container)
// console.log("|| PARSE || ", json, " || PARSE ||", typeof json)
// console.log("|| RESPONSE ||", JSON.parse(container.getAttribute("data")), "|| RESPONSE ||")

function getCookie(cname) {
    let name = cname + "=";
    let decodedCookie = decodeURIComponent(document.cookie);
    let ca = decodedCookie.split(';');
    for(let i = 0; i <ca.length; i++) {
      let c = ca[i];
      while (c.charAt(0) == ' ') {
        c = c.substring(1);
      }
      if (c.indexOf(name) == 0) {
        return c.substring(name.length, c.length);
      }
    }
    return "";
  }
//   console.log(getCookie("APILocalResponse"))
//   console.log(document.cookie)
//   let d = document.createElement("div")
//   let options = {}
//   var myModal = new bootstrap.Modal(d, options)
//   myModal.toggle()
//   myModal.hide()