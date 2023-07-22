import { Div, Table } from "../models/DOMLib.js"

export class ResponseView {
    constructor(response){
        this.el = document.createElement("div")
        if(response == null) return this
        this.response = response
        console.log(this.response)
        this.errores = this.response.errores || this.response.datosError || false
    }
    render(){
        this.el.innerHTML = ""
        let id = "table_err_"+this.response.id_proceso
        let table = new Table(
            id,
            ["ID Proceso", "Codigo Error", "Mensaje"]
        )
        if(!this.errores) return this.el
        for(let error of this.errores){
            let codigo = error.codigo
            let mensaje = error.mensaje
            table.addRow([
                this.response.id_proceso,
                codigo,
                mensaje
            ])
        }
        this.el.appendChild(table.render())
        return this.el
    }
}