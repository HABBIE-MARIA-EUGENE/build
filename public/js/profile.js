// /js/profile.js
$(function () {
  // cache elements
  const $fullName = $('#fullName');
  const $dob      = $('#dob');
  const $age      = $('#age');
  const $phone    = $('#phone');
  const $about    = $('#about');
  const $msg      = $('#msg');

  function showMsg(text, ok=false) {
    $msg.text(text)
       .removeClass('text-danger text-success')
       .addClass(ok ? 'text-success' : 'text-danger');
  }

  // enforce client-side session

  const sessionId = localStorage.getItem('sessionId');
  if (!sessionId) { window.location.href = 'login.html'; return; }

  // helper so .trim() never throws if an element is missing

  const valOrEmpty = $el => ($el && $el.length ? ($el.val() ?? '') : '');

  // Loading prof

  function loadProfile() {
    $.ajax({
      url: 'php/profile_get.php',
      method: 'POST',
      dataType: 'json',
      data: { session: sessionId }
    })
    .done(res => {
      if (res.status === 'ok') {
        const p = res.profile || {};
        $fullName.val(p.fullName || '');
        $dob.val(p.dob || '');
        $age.val(p.age ?? '');
        $phone.val(p.phone || '');
        $about.val(p.about || '');
        showMsg('Profile loaded', true);
      } else {
        showMsg(res.message || 'Failed to load profile');
        if (res.code === 'AUTH') {
          localStorage.removeItem('sessionId');
          window.location.href = 'login.html';
        }
      }
    })
    .fail(xhr => {
      showMsg('Server error while fetching profile');
      // reveal debug during dev
      try { console.warn('profile_get fail:', xhr.responseText); } catch {}
    });
  }
  loadProfile();

  // Save handler

  $('#saveBtn').on('click', function () {
    const payload = {
      session:  sessionId,
      fullName: valOrEmpty($fullName).trim(),
      dob:      valOrEmpty($dob),
      age:      valOrEmpty($age),
      phone:    valOrEmpty($phone).trim(),
      about:    valOrEmpty($about).trim(),
    };

    if (payload.fullName.length < 2) {
      showMsg('Full name too short'); return;
    }

    $.ajax({
      url: 'php/profile_update.php',
      method: 'POST',
      dataType: 'json',
      data: payload
    })
    .done(res => {
      if (res.status === 'ok') showMsg('Profile saved!', true);
      else {
        showMsg(res.message || 'Could not save profile');
        if (res.code === 'AUTH') {
          localStorage.removeItem('sessionId');
          window.location.href = 'login.html';
        }
      }
    })
    .fail(xhr => {
      showMsg('Server error while saving profile');
      try { console.warn('profile_update fail:', xhr.responseText); } catch {}
    });
  });

  // Logout
  
  $('#logoutBtn').on('click', function () {
    $.ajax({ url:'../php/logout.php', method:'POST', dataType:'json', data:{ session: sessionId } })
    .always(() => { localStorage.removeItem('sessionId'); window.location.href = 'login.html'; });
  });
});
