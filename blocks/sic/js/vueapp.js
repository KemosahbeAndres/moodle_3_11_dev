import './components/maincomponent.js';

var templatedata = document.getElementById("templatedata");
var data = JSON.parse(templatedata.getAttribute("json"));

new Vue({
    el: '#app'
});