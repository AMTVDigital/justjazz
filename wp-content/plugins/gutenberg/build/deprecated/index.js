window.wp=window.wp||{},window.wp.deprecated=function(e){var n={};function t(o){if(n[o])return n[o].exports;var r=n[o]={i:o,l:!1,exports:{}};return e[o].call(r.exports,r,r.exports,t),r.l=!0,r.exports}return t.m=e,t.c=n,t.d=function(e,n,o){t.o(e,n)||Object.defineProperty(e,n,{enumerable:!0,get:o})},t.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},t.t=function(e,n){if(1&n&&(e=t(e)),8&n)return e;if(4&n&&"object"==typeof e&&e&&e.__esModule)return e;var o=Object.create(null);if(t.r(o),Object.defineProperty(o,"default",{enumerable:!0,value:e}),2&n&&"string"!=typeof e)for(var r in e)t.d(o,r,function(n){return e[n]}.bind(null,r));return o},t.n=function(e){var n=e&&e.__esModule?function(){return e.default}:function(){return e};return t.d(n,"a",n),n},t.o=function(e,n){return Object.prototype.hasOwnProperty.call(e,n)},t.p="",t(t.s=372)}({32:function(e,n){e.exports=window.wp.hooks},372:function(e,n,t){"use strict";t.r(n),t.d(n,"logged",(function(){return r})),t.d(n,"default",(function(){return c}));var o=t(32),r=Object.create(null);function c(e){var n=arguments.length>1&&void 0!==arguments[1]?arguments[1]:{},t=n.version,c=n.alternative,i=n.plugin,u=n.link,a=n.hint,l=i?" from ".concat(i):"",d=t?" and will be removed".concat(l," in version ").concat(t):"",f=c?" Please use ".concat(c," instead."):"",p=u?" See: ".concat(u):"",s=a?" Note: ".concat(a):"",b="".concat(e," is deprecated").concat(d,".").concat(f).concat(p).concat(s);b in r||(Object(o.doAction)("deprecated",e,n,b),console.warn(b),r[b]=!0)}}}).default;