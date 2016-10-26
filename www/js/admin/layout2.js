Ext.QuickTips.init();

Ext.lib.Event.resolveTextNode = Ext.isGecko ? function(node){
    if(!node){
        return;
    }
    var s = HTMLElement.prototype.toString.call(node);
    if(s == '[xpconnect wrapped native prototype]' || s == '[object XULElement]'){
        return;
    }
    return node.nodeType == 3 ? node.parentNode : node;
} : function(node){
    return node && node.nodeType == 3 ? node.parentNode : node;
};

var aplikace = new Ext.Toolbar.Button({
    text    : "Application",
    defaultHandler: createGridApp,

    menu     : {
        items    : [
            {
                text:"TariffSpace",
                id:"TariffSpace",
                app:"TariffSpace",
                handler: createGridApp
            },
            {
                text:"Product",
                id:"Product",
                app:"Product",
                handler: createGridApp
            }
        ]
    }
});

var panel = new Ext.Panel({
    id            : 'mainPanel',
    layout        : 'fit',
    border        : false,
    tbar: [
        aplikace
    ],

    renderTo    : Ext.getBody()
});

Ext.onReady(function()
{
    Ext.BLANK_IMAGE_URL = baseUri + '../_public/js/extjs/resources/images/default/s.gif';
});

// window axis
x = 20;
y = 20;

/* handlers */
function getWindow(item, type)
{
    if(typeof item.modal == 'undefined')
        item.modal = false;

    if(typeof item.closable == 'undefined')
        item.closable = true;


    x = 20;
    y = 20;

    /* id of window */
    id = item.id + "-" + type;

    /* create if not exists */
    if(Ext.getCmp(id))
    {
        Ext.getCmp(id).show();
        return null;
    }

    /* create window */
    return new Ext.Window({
        closeAction    : "destroy",
        layout        : "fit",
        id            : id,
        modal        : item.modal,
        title        : item.text,
        width        : '95%',
        closable    : item.closable,
        renderTo    : 'mainPanel',
        x            : x,
        y            : y
    });
}

function createGridApp(item)
{
    if((appWindow = getWindow(item, "grid"))!==null)
    {
        // initialize application
        var app = new gridApp(item, appWindow, 30);
        app.initialize();
    }
}

function createFormApp(item, store, condition)
{
    if(condition)
        item.id = item.id + condition.id;

    if((appWindow = getWindow(item, "form"))!==null)
    {
        var app = new formApp(item, appWindow, store, condition);
        app.initialize();
    }
}
