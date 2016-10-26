var formApp = function(item, parent, store, condition)
{
	this.item 		= item;
	this.parent 	= parent;
	this.store		= store;
	this.condition 	= condition;


	this.initialize = function()
	{
		var errored = false;
		var app		= this;

		Ext.Ajax.request({
			url		: baseUri + 'configurator/createform/' + item.app,
			response: 'json',
			params	: app.condition,
			success	: function (response) 	{ try { app.render( JSON.parse(response.responseText)); } catch(e){ if(parent) parent.destroy(); Ext.Msg.alert('Chyba konfigurace', 'Nepodařilo se vytvořit okno. Kontaktujte správce systému.' + e.message); } },
			failure	: function (response)	{ Ext.Msg.alert('Chybná konfigurace', 'Nepodařilo se vytvořit okno. Kontaktujte správce systému.');	app.parent.destroy(); }
		});
	}

	this.render = function(settings)
	{
		parent.component = new Ext.form.FormPanel({
	        fileUpload	: true,
	        width		: 800,
	        autoHeight	: true,
	        frame		: true,
	        bodyStyle	: 'padding: 10px 10px 0 10px;',

	        defaults: {
	            anchor: '95%',
	            allowBlank: false,
	            msgTarget: 'side'
	        },

			items	: settings.columns,
			buttons	: [
			       	   	{ text: 'Save', handler:
			       	   			function()
			       	   			{
			       	   				if(parent.component.getForm().isValid()){
				       	   				try
				       	   				{
							       	   		 parent.component.getForm().submit({
							       	   			 	url		: settings.proxy,
								                    waitMsg	: 'Saving...',
								                    success	: function(form, o){
							       	   			 		if(store)
							       	   			 			store.reload();
							       	   			 	//	Ext.Msg.alert('Zpráva..', 'Data byla uložena!');
							       	   			 		parent.close();
							       	   		 		},
							       	   		 		failure	: function(form, o){
							       	   			 		Ext.Msg.alert('Error..', 'Exception!<br/>'+o.result.error);
							       	   		 		}

							       	   	 		});
				       	   				}
				       	   				catch(e)
				       	   				{
				       	   					if(console)
				       	   						console.log(e);
				       	   				}
			       	   				}
			       	   			} } ,
			       	   	{ text: 'Close', handler: function(){ parent.close(); } }
		       	  ]
		});

		parent.add(parent.component);

		for(var i = 0; i < settings.grids.length; i++)
		{
			var tabPanel = Ext.getCmp(settings.grids[i].renderTo + '_' + condition.id);
			var grid = new gridApp(settings.grids[i], tabPanel, 15);
			grid.initialize();
		}

		parent.show();
	}
}
