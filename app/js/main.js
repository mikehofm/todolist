
function renderTemplate(id, data) {
	renderTemplate.cache = renderTemplate.cache || {};
	
	if (!renderTemplate.cache[id])
		renderTemplate.cache[id] = Handlebars.compile($('#' + id).html());

	var template = renderTemplate.cache[id];
	return template(data);
}

Handlebars.registerHelper('lowercase', function(text) {
	if (typeof(text) !== 'string')
		return text;
		
	return text.toLowerCase();
});

Handlebars.registerHelper('capitalize', function(text) {
	if (typeof(text) !== 'string')
		return text;
		
	return text[0].toUpperCase() + text.substr(1);
});

$(function () {

	$('form[data-ajax]').submit(function (e) {
		e.preventDefault();
		var form = $(this);

		$.ajax(form.attr('action'), { type: this.method, data: form.serialize() })
			.done(function (response, textStatus, jqXHR) {
				form.trigger('ajax:done', response, textStatus, jqXHR);
			})
			.fail(function (jqXHR, textStatus, error) {
				form.trigger('ajax:fail', jqXHR, textStatus, error);
			});
	});
});