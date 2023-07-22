import { Table } from "../models/DOMLib.js"

export class ModuleView {
    constructor(module){
        this.module = module
        this.el = document.createElement("div")
        this.el.classList.add("card")
        this.el.style = "margin-top:2vh;margin-bottom:2vh;min-height:10vh;"
        this.activitys = []
        this.addActivitys(this.module.listaActividades)
        this.body = document.createElement("div")
        this.body.classList.add("card-body")
        this.header = document.createElement("div")
        this.header.className = "card-header"
    }
    addActivitys(activitys){
        for(let activity of activitys){
            this.activitys.push(activity)
        }
    }
    render(){
        this.el.innerHTML = ""
        this.body.innerHTML = ""
        this.header.innerHTML = this.module.codigoModulo
        let mTable = new Table(
            "table_mod_"+this.module.codigoModulo,
            ["Avance", "Conectividad", "Fecha Inicio", "Fecha Fin"]
        )
        mTable.addRow(
            [
                this.module.porcentajeAvance+" %",
                this.module.tiempoConectividad.toString().toHHMMSS(),
                this.module.fechaInicio,
                this.module.fechaFin
            ]
        )
        let subTitle = document.createElement("h3")
        subTitle.innerText = "Actividades:"
        let table = new Table(
            "table_act_"+this.module.codigoModulo,
            ["Nombre Actividad"]
        )
        for(let act of this.module.listaActividades){
            table.addRow([act.codigoActividad])
        }
        this.body.appendChild(mTable.render())
        this.body.appendChild(subTitle)
        this.body.appendChild(table.render())
        this.el.appendChild(this.header)
        this.el.appendChild(this.body)
        return this.el
    }
}