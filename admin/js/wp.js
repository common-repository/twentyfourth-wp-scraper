$(document).ready(() => {
    function toggleButtonStyle(btn, to_remove, to_add) {
        to_remove.forEach(element => {
            btn.removeClass(element)
        });
        to_add.forEach(element => {
            btn.addClass(element)
        });
    }

    function reload() {
        setTimeout(() => {
            try {
                window.localStorage.removeItem('tw_config')
            } catch (error) {
                console.log('done');
            }
            window.location.reload()
        }, 3000);
    }

    window.reAuth = function () {
        swal({
            title: 'Confirm',
            text: 'Will clear all previous data, enter your email and proceed. If you have an api token, you can enter it instead',
            content: "input",
        }).then(res => {
            if (res) {
                let url = `${window.api_service_url}/api/request-api-token`,
                    btn = $('.re-auth--btn'),
                    messages = $(".re-auth--feedback-details")

                runAuthentication(url, {
                    email: res,
                    accept_terms: 'on',
                    re_auth: true,
                }, 'on', null, messages, btn)
            }
        })
    }


    $("#upgrade-notif-btn").click(function (e) {
        e.preventDefault()
        $("#remove-upgrade-notification").submit();
    })

    $("#request-token--form").submit(function (e) {
        e.preventDefault()
        let btn = $("#request-token--form button.btn")
        let input = $("#request-token--form input#email")
        let terms = $("#request-token--form input#accept-terms")
        let messages = $("#feedback-details")
        let url = `${window.api_service_url}/api/request-api-token`;

        btn.attr('disabled', true)
        btn.html('Loading scraper, just a few.')

        var form_data = $(this).serialize()

        runAuthentication(url, form_data, terms.val(), input, messages, btn)
    })

    function runAuthentication(url, form_data, accept_terms, input = null, messages = null, btn = null) {
        axios.post(url, form_data).then(res => {
            if (res.data.success && btn) {
                toggleButtonStyle(btn, ['btn-primary'], ['btn-success'])
            } else if (input) {
                input.attr('type', 'email')
                toggleButtonStyle(btn, ['btn-primary'], ['btn-danger'])
            }

            btn.html(res.data.message)


            if (res.data.details) {
                messages.html(`<div class="alert alert-danger">${res.data.details}</div>`)
            }
            return res.data
        }).catch(err => {
            return {
                success: false
            }
        }).then(data => {
            let status = data.success
            if (status) {
                setTimeout(() => {
                    toggleButtonStyle(btn, ['btn-danger', 'btn-success'], ['btn-primary'])
                    btn.html('Saving configurations...')
                    data = {
                        api_token: data.api_token,
                        accept_terms: accept_terms,
                        action: "save_api_token"
                    }
                    data['_ajax_nonce'] = wp_scraper_ajax.nonce
                    $.post(wp_scraper_ajax.ajax_url, data, function (data) {
                        toggleButtonStyle(btn, ['btn-danger', 'btn-primary'], ['btn-success'])
                        btn.html('Complete! Reloading page in 3 seconds!')
                        reload()
                    });
                }, 450);
            } else {
                btn.attr('disabled', false)
                setTimeout(() => {
                    toggleButtonStyle(btn, ['btn-danger', 'btn-success'], ['btn-primary'])
                    btn.html('Try again')
                }, 1200);
            }
        })
    }
})