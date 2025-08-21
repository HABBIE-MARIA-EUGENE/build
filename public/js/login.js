$(function() {
    

    const $email = $('#email');
    const $pwd = $('#password');
    const $btn = $('#loginBtn');
    const $msg = $('#msg');

    
    function showMsg (text, ok=false) {
        $msg.text(text)
        .removeClass('text-danger text-success')
        .addClass(ok ? 'text-success': 'text-danger');
    }

    $btn.on('click', function(e) {

        e.preventDefault();

        const email = $email.val().trim();
        const pwd = $pwd.val();

        // client validation will add

        $.ajax({

            url:'php/login.php',
            method: 'POST',
            dataType:'json',
            data: { email: email, password: pwd}

        })

        .done (res => {

            if (res.status === 'ok') {
                localStorage.setItem('sessionId', res.session);
                console.log('login.php response:', res);
                showMsg('Login success ! Please wait while we redirecting', true);

                setTimeout(() => {
                window.location.href = 'profile.html';
            }, 500);

            } else {
                showMsg(res.message || 'Login failed !');
            }

        })

        .fail(xhr => {
            const m = (xhr.responseJSON && xhr.responseJSON.message)
                            ? xhr.responseJSON.message
                            : 'Server Error';
            showMsg(m);
                
        });

    });

});