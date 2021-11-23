(function ($,Themify) {
    'use strict';
        const _captcha = function (el) {
            const sendForm = function(form){
                    const data = new FormData(form[0]);
                    data.append("action", "builder_contact_send");
					data.append( 'post_id', form.data( 'post-id' ) );
					data.append( 'orig_id', form.data( 'orig-id' ) );
					data.append( 'element_id', form.data( 'element-id' ) );
                    if (form.find('[name="g-recaptcha-response"]').length > 0) {
                        data.append("contact-recaptcha", form.find('[name="g-recaptcha-response"]').val());
                    }
                    $.ajax({
                        url: form.prop('action'),
                        method: 'POST',
                        enctype: 'multipart/form-data',
                        processData: false,
                        contentType: false,
                        cache: false,
                        data: data,
                        success: function (response) {
							form.removeClass('sending');
							if ( response.success ) {
								form.find('.contact-message').html( '<p class="ui light-green contact-success">' + response.data.msg + '</p>' ).fadeIn();
								Themify.body.trigger( 'builder_contact_message_sent', [ form, response.data.msg ] );
								if ( response.data.redirect_url !== '' ) {
									window.location = response.data.redirect_url;
								}
								form[0].reset();
							} else {
								form.find('.contact-message').html( '<p class="ui red contact-error">' + response.data.error + '</p>' ).fadeIn();
								Themify.body.trigger( 'builder_contact_message_failed', [ form, response.data.error ] );
								
							}
							$('html').stop().animate({scrollTop: form.offset().top - 100}, 500, 'swing');
							if ( typeof grecaptcha === 'object' && form.find( '.themify_captcha_field' ).data( 'ver' ) === 'v2' ) {
								grecaptcha.reset();
							}
                        }
                    });
            },
            callback = function (el) {
                if (!Themify.is_builder_active) {
                    el.addEventListener('submit',function(e){
                        e.preventDefault();
                        const form = $(this);
                        if (form.hasClass('sending')) {
                            return false;
                        }
                        form.addClass('sending').find('.contact-message').fadeOut();
                        const cp = el.getElementsByClassName('themify_captcha_field')[0];
                        if( typeof cp !== 'undefined' && 'v3' === cp.dataset['ver'] && typeof grecaptcha !== 'undefined'){
                            grecaptcha.ready(function() {
                                grecaptcha.execute(cp.dataset['sitekey'], {action: 'captcha'}).then(function(token) {
                                    form.prepend('<input type="hidden" name="g-recaptcha-response" value="' + token + '">');
                                    sendForm(form);
                                });
                            });
                        }else{
                            sendForm(form);
                        }
                    });
                }
                el.addEventListener('reset', function () {
                    $(this).find('.builder-contact-field .control-input input[type="checkbox"]').prop('required', true);
                },{passive:true});
            },
            cp = el.getElementsByClassName('themify_captcha_field')[0];
            if (cp && typeof grecaptcha === 'undefined') {
                const key=cp.getAttribute('data-sitekey');
                if(key){
                    let url = 'https://www.google.com/recaptcha/api.js';
                    if( 'v3' === cp.getAttribute('data-ver')){
                        url+='?render='+key;
                    }
                    Themify.LoadAsync(url, callback.bind(null,el), false, true, function () {
                        return typeof grecaptcha !== 'undefined';
                    });
                }
            }
            else {
                callback(el);
            }
        };
        Themify.on('builder_load_module_partial', function(el,type,isLazy){
			if(isLazy===true && !el[0].classList.contains('module-contact')){
                return;
            }
            const forms = Themify.selectWithParent('builder-contact',el); 
            for(let i=forms.length-1;i>-1;--i){
				Themify.requestIdleCallback(function(){
					_captcha(forms[i]);
				},300);
            }
        });
}(jQuery,Themify));
