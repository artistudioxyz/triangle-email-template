    /**
     * Init
     * @contact
     * */
    init();
    function init(){
        /** Email send status */
        let html = `<i class="fas fas-sad-tear"></i> Email crashed, please turn off conflicting plugin and try again!`;
        jQuery('.form-result-false').html(html);
        jQuery('.form-result-true').html('Email successfully send!');
        animate('.form-result-false','animated FadeIn').show();
        animate('.form-result-true','animated FadeIn').show();
        /** Request data */
        jQuery.ajax({
            method: 'POST',
            url: 'admin-ajax.php',
            dataType : "json",
            data: {
                'action'    : 'triangle-emailtemplate-page-contact',
                'user_id'   : jQuery('#default-user').val(),
                'typeArgs'      : {
                    'numberposts': -1,
                    'orderby': 'post_title',
                },
                'userArgs'      : {
                    'fields': ['ID','display_name','user_email']
                },
            },
            success: function(data){
                setTimeout(function(){
                    animate('.form-result-true','animated FadeOut').hide();
                },5000)
                jQuery('#field-users').val('');
                load_field_templates(data);
                load_field_users(data);
            }
        });
    }

    /**
     * Validate contact form before submission
     * */
    jQuery('#contact-form').submit(function(e){
        let validation = validate_form({
            required: ['field_template', 'field_users', 'field_from_name', 'field_from_email', 'field_email_subject'],
            types: {'field_from_email': 'email'},
            messages: {'field_users': 'Please add user to the lists, by clicking + button!'}
        }, jQuery(this).serializeArray())
        if(!validation.status){
            jQuery('#form-message').html(validation.message);
            animate('#form-message', 'animated flash').show();
            e.preventDefault();
        }
    });

    /**
     * Load template field
     * */
    function load_field_templates(data){
        animate('#field-template-container', 'animated fadeIn').show();
        animate('#loading-field-template', 'animated fadeOut').hide();
        /** Load options */
        jQuery('#select-field-template').select2({
            data: data.templates.map((template) => {
                return {id: template.ID, text: template.post_title};
            })
        });
    }

    /**
     * Load user field
     * */
    function load_field_users(data){
        /** Current User */
        jQuery('#field-from-name').val(data.currentUser.data.display_name);
        jQuery('#field-from-email').val(data.currentUser.data.user_email);
        /** User lists options */
        animate('#field-user-container', 'animated fadeIn').show();
        animate('#loading-field-user', 'animated fadeOut').hide();
        /** Set default user */
        if(data.defaultUser){
            jQuery('#field-users').val(data.defaultUser.data.ID);
            jQuery('#user-lists').append(`<span class="badges"><i class="fas fa-times" data-user="${data.defaultUser.data.ID}"></i>${data.defaultUser.data.user_email}</span>`);
        }
        /** Load options */
        jQuery('#select-user-lists').select2({
            data: data.users.map((user) => {
                return {id: user.ID, text: `${user.display_name} - ${user.user_email}`};
            })
        });
    }

    /**
     * Trigger Add User to lists
     * */
    jQuery(document).on("click", "#add-user-to-lists", trigger_add_user_to_lists);
    function trigger_add_user_to_lists(){
        /** Set Data */
        let lists = jQuery('#field-users').val(),
            selected = jQuery('#select-user-lists').val(),
            selectedText = jQuery('#select-user-lists').text().replace(/\s/g,'');
            selectedText = selectedText.split('-')[1];
            selectedText = `<span class="badges"><i class="fas fa-times" data-user="${selected}"></i>${selectedText}</span>`;
        /** Validate selected user */
        let users = lists.split(',');
            users = users.filter((user) => {
                if(!user) return false; /** Validate Data */
                if(user==selected){ selectedText = ''; return false; } /** Find Duplicate */
                return true;
            });
            users.push(selected);
            users = users.join(',');
        /** Show user */
        jQuery('#field-users').val(users);
        jQuery('#user-lists').append(selectedText);
    }

    /**
     * Trigger Remove User from lists
     * */
    jQuery(document).on("click", ".badges i", trigger_remove_user_from_lists);
    function trigger_remove_user_from_lists(){
        /** Set Data */
        let selected = jQuery(this).attr('data-user'),
            lists = jQuery('#field-users').val();
        /** Remove user */
        let users = lists.split(',');
            users = users.filter((user) => {
                return !(user==selected);
            });
            users = users.join(',');
        /** Show user */
        jQuery('#field-users').val(users);
        jQuery(this).parent().remove();
    }