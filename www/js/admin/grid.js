var gridApp = function(item, parent, limit)
{
    this.item 		= item;
    this.parent 	= parent;
    this.settings 	= null;
    this.store		= null;
    this.limit		= limit;

    /**
     * load settings from ajax
     *-------------------------
     *
     * 1) STORE
     * 	a) fields to display
     * 	b) address
     *
     * 2) COLUMNS DEFINITIONS
     * 	a) column id
     *  b) header
     *  c) dataIndx
     *  d) width
     *  e) sortable
     *
     */
    this.initialize = function()
    {
        var errored = false;
        var app		= this;

        Ext.Ajax.request({
            url		: baseUri + 'configurator/creategrid/' + item.app,
            params	: app.item.condition,
            method	: 'post',
            response: 'json',
            success	: function (response) 	{ try { settings = JSON.parse(response.responseText); app.render(); } catch(e){ parent.destroy();
                if(!settings.success)
                {
                    Ext.Msg.alert('Error',settings.e);
                    return;
                }Ext.Msg.alert('Chyba konfigurace', 'Nepodařilo se vytvořit okno. Kontaktujte správce systému.' ); } },
            failure	: function () 			{ Ext.Msg.alert('Critical error', 'Create grid felt. Contact admin.');	}
        });
    };

    /**
     * crate grid
     * --------------------------
     * load settings
     *
     * craete grid component
     * from settings create app toolbar
     *
     * append to parent - window, panel, whatever
     */
    this.render	= function()
    {
        var app = this;

        // create the Data Store
        this.store = new Ext.data.JsonStore({
            root			: 'items',
            totalProperty	: 'totalCount',
            idProperty		: 'id',
            remoteSort		: true,
            fields			: settings.fields,

            // load using script tags for cross domain, if the data in on the same domain as
            // this page, an HttpProxy would be better
            proxy: new Ext.data.ScriptTagProxy({
                url		: settings.proxy.readUrl
            })
        });

        var pagingBar = new Ext.PagingToolbar({
            pageSize	: app.limit,
            store		: app.store,
            displayInfo	: true,
            displayMsg	: 'Shows {0} - {1} record {2}',
            emptyMsg	: 'No items'
        });

        columns = settings.columns;
        for( var i = 0, l = columns.length; i < l; ++i)
        {
            columns[i].editor = new Ext.form.TextField({allowBlank:false, clicksToEdit: 1});
        }

        var $singleSelect = true;
        var lHeight = 400;
        if(this.item.app=="Order" || this.item.app=="OrderSent"  || this.item.app=="PartnerOrder" || this.item.app=="PartnerOrderSent" )
        {
            $singleSelect = false;
            var lHeight = 700;
        }
        this.parent.component = new Ext.grid.EditorGridPanel({
            width					: '100%',
            height					: lHeight,
            store					: app.store,
            loadMask				: true,
            layout					: 'column',
            sm: new Ext.grid.RowSelectionModel({singleSelect: $singleSelect}),

            // grid columns
            columns: columns,

            // paging bar on the bottom
            tbar: createTBar(settings, this ),
            bbar: pagingBar,
            plugins:[new Ext.ux.grid.Search({
                iconCls:'icon-zoom',
                searchText: 'Search',
                minCharsTipText:'Insert at least {0} charakters',
                minChars:2,
                autoFocus:true
            })],
            listeners: {
                'rowdblclick': function(grid, index, rec){
                    try	{
                        selectedRow = grid.getSelectionModel().getSelections()[0].json;
                        condition = {id: selectedRow.id}
                        //	alert(condition.id);
                        //	console.log(grid.tbar);
                    } catch(e) {
                        Ext.Msg.alert('Error', 'Please select record !');
                    }

                }
            }

        });
        this.parent.add(this.parent.component);

        // trigger the data store load
        this.store.load({params:{start:0, limit:app.limit}});

        this.parent.doLayout();
        this.parent.show();

    };
};

var createTBar = function( settings, app, condition ){
    var actionAdd = new Ext.Action({
        text		: 'Add',
        handler		:
            function(btn,e){
                if(typeof app.item.condition != 'undefined' && typeof app.item.condition.id != 'undefined')
                    app.item.condition.id = null;
                createFormApp(app.item, app.parent.component.store, app.item.condition);
            }
    });

    var actionEdit = new Ext.Action({
        text		: 'Edit',
        handler		:
            function(btn,e)
            {
                try	{
                    selectedRow = app.parent.component.getSelectionModel().getSelections()[0].json;

                    if(app.item.condition)
                    {
                        condition = app.item.condition;
                        condition['id'] = selectedRow.id;
                    }
                    else
                        condition = {id: selectedRow.id}

                    createFormApp(app.item, app.parent.component.store, condition);
                } catch(e) {
                    Ext.Msg.alert('Error', 'Select record row!');
                }
            },

        scope: window
    });
    var deleteLabel = 'Delete';
    var actionDelete = new Ext.Action({
        text		: deleteLabel,
        handler		:
            function(btn,e)
            {
                try {
                    var selectedRow = app.parent.component.getSelectionModel().getSelections()[0].json;
                    Ext.MessageBox.confirm('', 'Really delete "' + selectedRow.label + '"?'  ,
                        function(btn)
                        {
                            if(btn=='yes')
                            {
                                Ext.Ajax.request({
                                    url		: settings.proxy.deleteUrl + '?id=' + selectedRow.id,
                                    method	: 'get',
                                    response: 'json',
                                    success	: function (response) 	{ app.parent.component.store.reload(); var data = JSON.parse(response.responseText);
                                        if(!data.success)
                                        {
                                            Ext.Msg.alert('Error',data.e); return;
                                        }
                                        else
                                        {
                                            Ext.Msg.alert('Success', 'Record deleted"' ); return;
                                        } },
                                    failure	: function () 			{ Ext.Msg.alert('Critical error', 'Couldnt delete record.');	}
                                });
                            }
                        });
                } catch ( e ) {
                    Ext.Msg.alert('Error', 'Select record row!')
                }
            },
        scope: window
    });

    return [ actionAdd, actionEdit, actionDelete ];
};
