Handlebars.templates = Handlebars.templates || {};
(function($){ 
    $(document).ready(function() {
        $("script[type='text/x-handlebars']").each(function() {
            var template = Handlebars.compile($(this).html());
            if($(this).data("partial")) {
                var name = $(this).data("partial-name");
                Handlebars.registerPartial(name, template);
            }

            var template_name = $(this).data("name");
            if(template_name) {
                Handlebars.templates[template_name] = template;
            } else if(window.console && !$(this).data("partial-name")) {
                console.warn("The template name is not set. Specify the 'data-name' attribute on the script element.");
            }
        });
    });
}(jQuery));

function render(template_name, context, parent, template) {
    if(!template) {
        template = Handlebars.templates[template_name];
    }

    if(template) {
        var html = template(context);
        if(parent !== undefined) {
            $(parent).html(html);
            return true;
        } else {
            return html;
        }
    } else if(window.console) {
        console.error("The template '"+template_name+"' doesn't exists.");
    }
}