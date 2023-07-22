import { Div, Table } from "../models/DOMLib.js"
import { ModuleView } from "./ModuleView.js"
import { Pager } from "./Pager.js"

export class RequestView {
    constructor(request){
        this.request = request
        this.students = request.listaAlumnos || []
        this.views = []
        this.el = document.createElement("div")
        this.el.className = "container-fluid"
        this.body = document.createElement("div")
        this.body.className = "row rows-cols-1 rows-cols-sm-2"
    }
    render(){
        this.el.innerHTML = ""
        this.body.innerHTML = ""
        let leftCol = document.createElement("div")
        leftCol.className = "col col-lg-3"
        let keys = Object.keys(this.students[0]).filter((value, index, arr) => {
            return value != "tiempoConectividad" && value != "porcentajeAvance" && value != "listaModulos"
        })
        let rt = keys.shift() + "-" + keys.shift()
        let lTable = new Table(
            "student_list_",
            ["RUT Alumno", "Avance", "Conectividad"]
        )
        let rightCol = document.createElement("div")
        rightCol.className = "col col-lg-9"
        const pager = new Pager(rightCol, false)
        
        for(let student of this.students){
            let rut = student.rutAlumno + "-" + student.dv
            let path = "st_"+rut
            let sTable = new Table(
                "st_view_"+rut,
                keys
            )
            sTable.addRow(
                [
                    student.estado,
                    student.fechaInicio,
                    student.fechaFin,
                    student.fechaEjecucion
                ]
            )
            let modulos = student.listaModulos || []
            let modulesView = []

            for(let modulo of modulos){
                modulesView.push(new ModuleView(modulo))
            }
            pager.addPage(path, {
                render: ()=>{
                    let div = document.createElement("div")
                    let h3 = document.createElement("h3")
                    h3.innerText = "Alumno: "+rut
                    div.appendChild(h3)
                    let mTable = sTable
                    div.appendChild(mTable.render())
                    for(let view of modulesView){
                        div.appendChild(view.render() || view)
                    }
                    return div
                }
            })
            lTable.addRow(
                [
                    rut,
                    student.porcentajeAvance + "%",
                    (student.tiempoConectividad).toString().toHHMMSS()
                ],
                ()=>{
                    pager.show(path)
                }
            )
            
        }
        leftCol.appendChild(lTable.render())
        pager.showFirst()
        this.body.appendChild(leftCol)
        this.body.appendChild(rightCol)
        this.el.appendChild(this.body)
        return this.el
    }
}