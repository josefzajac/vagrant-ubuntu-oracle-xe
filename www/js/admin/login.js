Ext.onReady(function() {
    var odhlaseni = new Ext.Action( {
        text : "Signout",
        handler : function() {
            Ext.Ajax.request( {
                url : baseUri + 'homepage/logout',
                method : 'post',
                response : 'json',
                success : function(response) {
                    message = JSON.parse(response.responseText);
                    if (message.success)
                    {
                        window.show();
                        Ext.Msg.alert('', 'Success signout.');
                    }
                    else
                        Ext.Msg.alert('Signout error', message.e);
                },
                failure : function() {
                    Ext.Msg.alert('Signout error','signout 500.');
                }
            })
        }
    });

    var window = getWindow( {
        id : 'loginWindow',
        text : 'Login',
        modal : true,
        closable : false
    });

    var loginform = new Ext.form.FormPanel( {
        width : 800,
        autoHeight : true,
        frame : true,
        bodyStyle : 'padding: 10px 10px 0 10px;',
        defaults : {
            anchor : '95%',
            allowBlank : false,
            msgTarget : 'side'
        },
        items : [
            {id : 'login',name : 'login',fieldLabel : 'Username',xtype : 'textfield'},
            {id : 'password',name : 'password',fieldLabel : 'Password',xtype : 'textfield',inputType : 'password'} ],
        buttons : [ {
            text : 'Login',
            handler : function() {
                Ext.Ajax.request( {
                    url : baseUri + 'homepage/login',
                    method : 'post',
                    response : 'json',
                    params : {
                        'login' : Ext.getCmp('login').getValue(),
                        'password' : Ext.ux.util.MD5(Ext.getCmp('password').getValue())
                    },
                    success : function(response) {
                        message = JSON.parse(response.responseText);
                        if (message.success)window.hide();
                        else	Ext.Msg.alert('Signout error', message.e);
                    },
                    failure : function() {
                        Ext.Msg.alert('Signout error','siggnout 500');
                    }

                })
                Ext.getCmp('password').setValue('');
            }
        } ]
    });
    window.add(loginform);

    Ext.getCmp('mainPanel').getTopToolbar().add(odhlaseni);
    Ext.Ajax.request( {
        url : 'homepage/check-user',
        response : 'json',
        success : function(response) {
            message = JSON.parse(response.responseText);
            if (!message.success)
                window.show();
        }
    })
});








