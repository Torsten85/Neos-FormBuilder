'use strict';var repatchaReady=function repatchaReady(){var widgets=document.querySelectorAll('.g-recaptcha');var _iteratorNormalCompletion=true;var _didIteratorError=false;var _iteratorError=undefined;try{var _loop=function _loop(){var widget=_step.value;var sitekey=widget.dataset.sitekey;var theme=widget.dataset.theme;var input=widget.nextElementSibling;grecaptcha.render(widget,{sitekey:sitekey,theme:theme,callback:function callback(response){input.value=response},'expired-callback':function expiredCallback(){input.value=''}})};for(var _iterator=widgets[Symbol.iterator](),_step;!(_iteratorNormalCompletion=(_step=_iterator.next()).done);_iteratorNormalCompletion=true){_loop()}}catch(err){_didIteratorError=true;_iteratorError=err}finally{try{if(!_iteratorNormalCompletion&&_iterator.return){_iterator.return()}}finally{if(_didIteratorError){throw _iteratorError}}}};document.addEventListener('Neos.PageLoaded',repatchaReady,false);