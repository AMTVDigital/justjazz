!function(e){function t(t){for(var n,a,c=t[0],u=t[1],l=t[2],d=0,g=[];d<c.length;d++)a=c[d],Object.prototype.hasOwnProperty.call(i,a)&&i[a]&&g.push(i[a][0]),i[a]=0;for(n in u)Object.prototype.hasOwnProperty.call(u,n)&&(e[n]=u[n]);for(s&&s(t);g.length;)g.shift()();return o.push.apply(o,l||[]),r()}function r(){for(var e,t=0;t<o.length;t++){for(var r=o[t],n=!0,a=1;a<r.length;a++){var c=r[a];0!==i[c]&&(n=!1)}n&&(o.splice(t--,1),e=__webpack_require__(__webpack_require__.s=r[0]))}return e}var n={},i={10:0},o=[];function __webpack_require__(t){if(n[t])return n[t].exports;var r=n[t]={i:t,l:!1,exports:{}};return e[t].call(r.exports,r,r.exports,__webpack_require__),r.l=!0,r.exports}__webpack_require__.e=function(e){var t=[],r=i[e];if(0!==r)if(r)t.push(r[2]);else{var n=new Promise((function(t,n){r=i[e]=[t,n]}));t.push(r[2]=n);var o,a=document.createElement("script");a.charset="utf-8",a.timeout=120,__webpack_require__.nc&&a.setAttribute("nonce",__webpack_require__.nc),a.src=function(e){return __webpack_require__.p+""+({1:"chunk-googlesitekit-adminbar~chunk-googlesitekit-setup-wizard~chunk-googlesitekit-setup-wizard-proxy",2:"vendors~chunk-googlesitekit-adminbar~chunk-googlesitekit-setup-wizard-proxy",4:"chunk-googlesitekit-adminbar",21:"vendors~chunk-googlesitekit-adminbar"}[e]||e)+"-"+{1:"2422cbe0801b8fe596c6",2:"afa5462390409683f6d7",4:"0e532f3d6c0b4943f6fd",21:"711f81202b21622c492a"}[e]+".js"}(e);var c=new Error;o=function(t){a.onerror=a.onload=null,clearTimeout(u);var r=i[e];if(0!==r){if(r){var n=t&&("load"===t.type?"missing":t.type),o=t&&t.target&&t.target.src;c.message="Loading chunk "+e+" failed.\n("+n+": "+o+")",c.name="ChunkLoadError",c.type=n,c.request=o,r[1](c)}i[e]=void 0}};var u=setTimeout((function(){o({type:"timeout",target:a})}),12e4);a.onerror=a.onload=o,document.head.appendChild(a)}return Promise.all(t)},__webpack_require__.m=e,__webpack_require__.c=n,__webpack_require__.d=function(e,t,r){__webpack_require__.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:r})},__webpack_require__.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},__webpack_require__.t=function(e,t){if(1&t&&(e=__webpack_require__(e)),8&t)return e;if(4&t&&"object"==typeof e&&e&&e.__esModule)return e;var r=Object.create(null);if(__webpack_require__.r(r),Object.defineProperty(r,"default",{enumerable:!0,value:e}),2&t&&"string"!=typeof e)for(var n in e)__webpack_require__.d(r,n,function(t){return e[t]}.bind(null,n));return r},__webpack_require__.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return __webpack_require__.d(t,"a",t),t},__webpack_require__.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},__webpack_require__.p="",__webpack_require__.oe=function(e){throw console.error(e),e};var a=window.webpackJsonp=window.webpackJsonp||[],c=a.push.bind(a);a.push=t,a=a.slice();for(var u=0;u<a.length;u++)t(a[u]);var s=c;o.push([401,0]),r()}({40:function(e,t,r){"use strict";(function(e){r.d(t,"a",(function(){return i})),r.d(t,"b",(function(){return a}));var n=r(54),i="_googlesitekitDataLayer",o="data-googlesitekit-gtag";function a(t,r){var a=Object(n.a)(r);return function(){t.trackingEnabled=!0;var r=e.document;if(!r.querySelector("script[".concat(o,"]"))){var n=r.createElement("script");n.setAttribute(o,""),n.async=!0,n.src="https://www.googletagmanager.com/gtag/js?id=".concat(t.trackingID,"&l=").concat(i),r.head.appendChild(n),a("js",new Date),a("config",t.trackingID)}}}}).call(this,r(16))},401:function(e,t,r){"use strict";r.r(t),function(e){var t=r(72),n=r(45);e.googlesitekitAdminbar&&e.googlesitekitAdminbar.publicPath&&(r.p=e.googlesitekitAdminbar.publicPath);var i=!1;function o(){Promise.all([r.e(2),r.e(21),r.e(1),r.e(4)]).then(r.bind(null,409)).then((function(e){return e})).catch((function(){return new Error("Site Kit: An error occurred while loading the Adminbar component files.")})).then((function(e){try{e.init()}catch(e){console.error("Site Kit: An error occurred while loading the Adminbar components."),document.getElementById("js-googlesitekit-adminbar").classList.add("googlesitekit-adminbar--has-error")}document.getElementById("js-googlesitekit-adminbar").classList.remove("googlesitekit-adminbar--loading")}))}e.addEventListener("load",(function(){var r=document.getElementById("wp-admin-bar-google-site-kit");if(r&&e.localStorage){var a=e.localStorage.getItem("googlesitekit::total-notifications")||0;Object(t.a)(a);var c=function(){i||(Object(n.c)("admin_bar","page_stats_view"),o(),i=!0)};"true"===Object(t.c)("googlesitekit_adminbar_open")?(c(),r.classList.add("hover")):r.addEventListener("mouseenter",c,!1)}}))}.call(this,r(16))},45:function(e,t,r){"use strict";(function(e){r.d(t,"a",(function(){return s})),r.d(t,"b",(function(){return d})),r.d(t,"c",(function(){return l}));var n=r(76),i=e._googlesitekitBaseData||{},o={isFirstAdmin:i.isFirstAdmin,trackingEnabled:i.trackingEnabled,trackingID:i.trackingID,referenceSiteURL:i.referenceSiteURL,userIDHash:i.userIDHash},a=Object(n.a)(o),c=a.enableTracking,u=a.disableTracking,s=a.isTrackingEnabled,l=a.trackEvent;function d(e){e?c():u()}d(s())}).call(this,r(16))},54:function(e,t,r){"use strict";r.d(t,"a",(function(){return i}));var n=r(40);function i(e){return function(){e[n.a]=e[n.a]||[],e[n.a].push(arguments)}}},72:function(e,t,r){"use strict";(function(e){r.d(t,"a",(function(){return a})),r.d(t,"b",(function(){return c})),r.d(t,"c",(function(){return s}));var n=r(50),i=r.n(n),o=r(0),a=function(){var e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:0,t=null,r=null,n=document.querySelector("#toplevel_page_googlesitekit-dashboard .googlesitekit-notifications-counter"),i=document.querySelector("#wp-admin-bar-google-site-kit .googlesitekit-notifications-counter");if(n&&i)return!1;if(t=document.querySelector("#toplevel_page_googlesitekit-dashboard .wp-menu-name"),r=document.querySelector("#wp-admin-bar-google-site-kit .ab-item"),null===t&&null===r)return!1;var a=document.createElement("span");a.setAttribute("class","googlesitekit-notifications-counter update-plugins count-".concat(e));var c=document.createElement("span");c.setAttribute("class","plugin-count"),c.setAttribute("aria-hidden","true"),c.textContent=e;var u=document.createElement("span");return u.setAttribute("class","screen-reader-text"),u.textContent=Object(o.sprintf)(
/* translators: %d is the number of notifications */
Object(o._n)("%d notification","%d notifications",e,"google-site-kit"),e),a.appendChild(c),a.appendChild(u),t&&null===n&&t.appendChild(a),r&&null===i&&r.appendChild(a),a},c=function(){e.localStorage&&e.localStorage.clear(),e.sessionStorage&&e.sessionStorage.clear()},u=function(e){for(var t=location.search.substr(1).split("&"),r={},n=0;n<t.length;n++)r[t[n].split("=")[0]]=decodeURIComponent(t[n].split("=")[1]);return e?r.hasOwnProperty(e)?decodeURIComponent(r[e].replace(/\+/g," ")):"":r},s=function(e){var t=arguments.length>1&&void 0!==arguments[1]?arguments[1]:location,r=new URL(t.href);if(e)return r.searchParams&&r.searchParams.get?r.searchParams.get(e):u(e);var n={},o=!0,a=!1,c=void 0;try{for(var s,l=r.searchParams.entries()[Symbol.iterator]();!(o=(s=l.next()).done);o=!0){var d=i()(s.value,2),g=d[0],f=d[1];n[g]=f}}catch(e){a=!0,c=e}finally{try{o||null==l.return||l.return()}finally{if(a)throw c}}return n}}).call(this,r(16))},76:function(e,t,r){"use strict";(function(e){r.d(t,"a",(function(){return s}));var n=r(20),i=r.n(n),o=r(40),a=r(77);function c(e,t){var r=Object.keys(e);return Object.getOwnPropertySymbols&&r.push.apply(r,Object.getOwnPropertySymbols(e)),t&&(r=r.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),r}var u={isFirstAdmin:!1,trackingEnabled:!1,trackingID:"",referenceSiteURL:"",userIDHash:""};function s(t){var r=arguments.length>1&&void 0!==arguments[1]?arguments[1]:e,n=function(e){for(var t=1;t<arguments.length;t++){var r=null!=arguments[t]?arguments[t]:{};t%2?c(r,!0).forEach((function(t){i()(e,t,r[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(r)):c(r).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(r,t))}))}return e}({},u,{},t);return n.referenceSiteURL&&(n.referenceSiteURL=n.referenceSiteURL.toString().replace(/\/+$/,"")),{enableTracking:Object(o.b)(n,r),disableTracking:function(){n.trackingEnabled=!1},isTrackingEnabled:function(){return!!n.trackingEnabled},trackEvent:Object(a.a)(n,r)}}}).call(this,r(16))},77:function(e,t,r){"use strict";r.d(t,"a",(function(){return i}));var n=r(54);function i(e,t){var r=Object(n.a)(t);return function(t,n){var i=arguments.length>2&&void 0!==arguments[2]?arguments[2]:"",o=arguments.length>3&&void 0!==arguments[3]?arguments[3]:"",a=e.isFirstAdmin,c=e.referenceSiteURL,u=e.trackingEnabled,s=e.trackingID,l=e.userIDHash;u&&r("event",n,{send_to:s,event_category:t,event_label:i,event_value:o,dimension1:c,dimension2:a?"true":"false",dimension3:l})}}}});