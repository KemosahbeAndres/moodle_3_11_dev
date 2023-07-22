import { BaseComponent } from "../models/BaseComponent.js"
import { BasePresenter } from "../presenters/BasePresenter.js"

export class Section extends BaseComponent{
    constructor(name, active, content){
        super()
        this.text = name
        this.id = name.toLowerCase() + "_section"
        this.active = active
        this.views = []
        if(content instanceof BasePresenter) this.views.push(content)
    }
    addView(view){
        if(view instanceof BasePresenter){
            this.views.push(view)
        }
    }
    addViews(views){
        if(Array.isArray(views) && views.length > 0){
            for(let view of views){
                this.addView(view)
            }
        }
    }
    render(){
        let body = document.createElement("div")
        body.classList.add("tab-pane")
        body.classList.add("fade")
        if(this.active){
            body.classList.add("show")
            body.classList.add("active")
        }
        body.id = this.id
        body.setAttribute("role", "tabpane")
        body.setAttribute("aria-labelledby", this.text.toLowerCase() + "-tab")
        if(this.views.length > 0){
            for(let view of this.views){
                if(view instanceof BasePresenter){
                    body.appendChild(view.render())
                }
            }
        }
        return body
    }
}