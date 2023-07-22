import { Button, Icon } from './../models/DOMLib.js'

export class Pager {
    constructor(parent, header = true){
        this.pages = []
        this.parent = parent
        this.selected = 0
        this.history = []
        this.pathSelected = ""
        this.flag = header
    }
    searchPage(path, list){
        for(const page in list){
            if(path == list[page].path){
                return page
            }
        }
        return false
    }
    addPages(pages = []){
        if(pages.length < 0) return false
        for(let page of pages){
            this.addPage(page.path, page.view)
        }
    }
    addPage(path, view){
        this.pages.push(new Page(path, view))
    }
    renderNav(){
        if(!this.flag) return
        let icon = new Icon("arrow-left", {style:"font-size: 20pt !important;"})
        let prev = new Button("", (e) => {
            e.preventDefault()
            this.previous()
        })
        prev.addChild(icon)
        let btns = document.createElement("div")
        btns.appendChild(prev.render())
        this.parent.appendChild(btns)
    }
    showFirst(){
        this.show(this.pages[0].path)
    }
    show(path){
        let page = this.searchPage(path, this.pages)
        let view = this.pages[page].render() || this.pages[page]
        this.parent.innerHTML = ""
        this.renderNav()
        this.parent.appendChild(view)
        if(this.selected < this.history.length - 1){
            this.history.splice(this.selected + 1)
        }
        this.history.push(page)
        this.selected = this.history.length - 1
        this.pathSelected = path
    }
    previous(){
        this.selected -= 1
        if(this.selected < 0) this.selected = 0
        let view = this.pages[this.history[this.selected]].render()
        this.pathSelected = this.pages[this.history[this.selected]].path
        this.parent.innerHTML = ""
        this.renderNav()
        this.parent.appendChild(view)
    }
    next(){
        this.selected += 1
        if(this.selected > (this.history.length - 1)) this.selected = (this.history.length - 1)
        this.parent.innerHTML = ""
        let view = this.pages[this.history[this.selected]].render()
        this.pathSelected = this.pages[this.history[this.selected]].path
        this.parent.innerHTML = ""
        this.renderNav()
        this.parent.appendChild(view)
    }
    where(){
        return this.pathSelected
    }
}
export class Page {
    constructor(path, view){
        this.path = path
        this.view = view
    }
    render(){
        return this.view.render()      
    }
}