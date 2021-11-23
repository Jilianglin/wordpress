tinymce.PluginManager.add( 'themifyTooltip', function( editor, url ) {

	editor.addButton( 'btnthemifyTooltip', {
		type: 'button',
		text: '',
		image: themifyTooltip.icon,
		tooltip: themifyTooltip.i18n.menuTooltip,
		onclick: function() {
			let selectedContent = editor.selection.getContent();
			let fields = [
				{ type : 'textbox', label : themifyTooltip.i18n.textLbl, name : 'text', value: selectedContent },
				{ type : 'textbox', label : themifyTooltip.i18n.tooltipLbl, name : 'tooltip', multiline : true, minHeight : 70 }
			];
			editor.windowManager.open({
				title : themifyTooltip.i18n.windowTitle,
				body : fields,
				onSubmit : function( e ){
					let values = this.toJSON(); // get form field values
					editor.insertContent( '<span tabindex="0" class="tf_tooltip">' + values.text + '<span class="tf_tooltip_content">' + values.tooltip + '</span></span>' );
				}
			});
		}
	} );

} );