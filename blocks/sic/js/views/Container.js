import { BaseComponent } from "../models/BaseComponent.js";
import { Section } from './Section.js'

export class Container extends BaseComponent {
    constructor(name){
        super()
        this.sections = []
        this.id = name || "tabContent"
    }
    createSection(name, active = false, content){
        let section = new Section(name, active, content)
        this.sections.push(section)
        return section
    }
    getSections(){
        return this.sections
    }
    render(){
        let content = document.createElement("div")
        content.classList.add("tab-content")
        content.id = this.id
        for(let section of this.sections){
            content.appendChild(section.render())
        }
        return content
    }
}