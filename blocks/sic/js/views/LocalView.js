import { LocalResponsePresenter } from "../presenters/LocalResponsePresenter.js";
import { Table, Button, Icon } from './../models/DOMLib.js'
import { RecordView } from './RecordView.js'
import { Pager } from "./Pager.js";

export class LocalView extends LocalResponsePresenter {
    constructor(interactor, title = ""){
        super()
        this.el = document.createElement("div")
        this.el.className = "card mx-1 my-2 border-0"
        this.interactor = interactor
        this.body = document.createElement("div")
        this.body.classList.add("card-body")
        this.students = []
        this.errors = []
    }
    render(){
        let datafields = this.interactor.downloadData()
        let div = document.createElement("div")
        div.classList.add("container-fluid")
        
        const pager = new Pager(div)
        let table = new Table(
            "table_"+1,
            ["ID", "Fecha y Hora", "Datos Enviados", "Respuesta"],
            {},
            ["table-hover"]
        )
        if(Array.isArray(datafields)){
            for(let record of datafields){
                let view =  new RecordView(record, pager)
                pager.addPage((record.date).toString(), view)
                let date = new Date(record.date * 1000)
                let fecha = date.getUTCDate() + "/" + date.getUTCMonth() + "/" + date.getFullYear() + " " + date.getHours()+":"+date.getMinutes()+":"+date.getSeconds()
                table.addRow([
                    record.date,
                    fecha,
                    record.request == null ? "No" : "Si",
                    record.response == null ? "No" : "Si"
                ], (e) => {
                    pager.show((record.date).toString())
                })
            }
        }
        
        let tab = new Table(
            "table_"+1,
            ["JAJAJ", "funciona"]
        )
        tab.addRow(["jeje", "linko"], (e) => {
            pager.show('home')
        })
        pager.addPage('home', table)
        pager.addPage('home/api', tab)
        pager.show('home')
        // console.log("PAGES:", pager.pages)        

        // this.body.appendChild(btns)
        this.body.appendChild(div)
        this.el.appendChild(this.body)
        return this.el
    }
}