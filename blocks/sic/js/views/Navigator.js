import { BaseComponent } from "./../models/BaseComponent.js"

export class Nav extends BaseComponent{
    constructor(sections){
        super()
        this.links = []
        this.sections = sections || []
    }
    createLink(name, target, active = false, id = null, href = "#navigator"){
        let section = new NavItem(
            name,
            active,
            id,
            "#"+href,
            target
        )
        this.links.push(section)
        return section
    }
    render(){
        let body = document.createElement("div")
        body.id = "navigator"
        let navTab = document.createElement("ul")
        navTab.classList.add("nav")
        navTab.classList.add("nav-tabs")
        if(this.sections.length > 0){
            for(let section of this.sections){
                let link = this.createLink(
                    section.text,
                    section.id,
                    section.active,
                    section.text.toLowerCase() + "-tab",
                    body.id
                )
                navTab.appendChild(link.render())
            }
        }
        body.appendChild(navTab)
        return body
    }
}

class NavItem {
    constructor(name, active, id, href, target){
        this.linkName = name
        this.active = active
        this.id = id
        this.href = href
        this.target = "#" + target
    }
    render(){
        let li = document.createElement("li")
        let a = document.createElement("a")
        li.classList.add("nav-item")
        a.classList.add("nav-link")
        if(this.active){
            a.classList.add("active")
            a.setAttribute("aria-selected", true)
        }else{
            a.setAttribute("aria-selected", false)
        }
        if(this.id != null) a.id = this.id
        a.innerText = this.linkName
        a.href = this.href
        a.setAttribute("data-bs-toggle", "tab")
        a.setAttribute("data-bs-target", this.target)
        a.setAttribute("role", "tab")
        a.setAttribute("aria-controls", this.linkName.toLowerCase())
        li.appendChild(a)
        return li
    }
}
