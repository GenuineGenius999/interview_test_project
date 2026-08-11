require('./bootstrap');

window.Vue = require('vue').default;

Vue.component('hello-world', require('./components/HelloWorld.vue').default);

new Vue({
    el: '#app',
});
