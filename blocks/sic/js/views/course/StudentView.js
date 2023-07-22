import { ModuleView } from './ModuleView.js'

export class StudentView {
    constructor(student){
        this.student = student
        this.el = document.createElement("div")
        this.el.classList.add("border-0")
        this.el.classList.add("card")
        this.el.id = "student_card_"+ Math.round(Math.random()*10000)
        this.body = document.createElement("div")
        this.body.className += "card-body border"
        this.body.style = "border-radius: 0.25rem;"
        this.modules = []
        console.log("StudentView:", student)
        this.addmodules(this.student.listaModulos || [])
    }
    addmodules(modules){
        if(!Array.isArray(modules)) return false
        for(let module of modules){
            this.modules.push(new ModuleView(module))
        }
    }
    render(){
        this.el.className += " p-2"

        let modules_container = document.createElement("div")
        modules_container.id = "modules"
        modules_container.className += "row row-cols-2"
        if(this.modules.length > 0){
            for(let modulo of this.modules){
                console.log("Modulo:", modulo)
                modules_container.appendChild(modulo.render())
            }
        }
        this.body.appendChild(modules_container)
        this.el.appendChild(this.body)
        return this.el
    }
}