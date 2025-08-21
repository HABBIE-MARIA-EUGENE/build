$(function() {
    const $email = $('#email');
    const $pwd = $('#password');
    const $btn = $('#registerBtn');
    const $msg = $('#msg');



    $btn.on('click',function() {
        const email = $email.val();
        const pwd = $pwd.val();

        //validating mail

        $.ajax( {
            url:'./php/register.php',
            method:'POST',
            dataType:'json',
            data: { email: email, password: pwd}
        })

        .done(res => {

            if (res.status === 'ok') {
                $msg.text('Registered Succesfully')
                .removeClass().addClass('text-success');
            }

            else {
                $msg.text(res.message || 'Registered failed')
                .removeClass().addClass('text-danger');
            }
        })


        .fail(xhr => {
            const m = (xhr.responseJSON && xhr.responseJSON.message)
            ? xhr.responseJSON.message
            : 'Server Error js !';
            $msg.text(m).removeClass().addClass('text-danger');
        })

    })

})