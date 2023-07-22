import { RemoteResponsePresenter } from "../presenters/RemoteResponsePresenter.js"
export class RemoteView extends RemoteResponsePresenter {
    constructor(){
        super()
        this.el = document.createElement("div")
    }
    render(){
        return this.el
    }
}