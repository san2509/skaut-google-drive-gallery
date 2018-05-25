"use strict";
jQuery( document ).ready(function($) {
	var path = [];
	var localize = [];

	tinymce.PluginManager.add("sgdg_tinymce_button", function(editor, url) {
		editor.addButton("sgdg_tinymce_button", {
			text: "SGDG", // TODO: icon
			icon: false,
			onclick: function() {tinymce_onclick(editor);}
		});
		localize = editor.settings.sgdg_localize;
		var html = "<div id=\"sgdg-tinymce-modal\"></div>";
		$("body").append(html)
	});
	function tinymce_onclick(editor)
	{
		tinymce_html(editor);
		tb_show(localize.dialog_title, "#TB_inline?inlineId=sgdg-tinymce-modal");
		path = [];
		ajax_query();
	}
	function tinymce_html(editor)
	{
		var html = "<table id=\"sgdg-tinymce-table\" class=\"widefat\">";
		html += "<thead>";
		html += "<tr>";
		html += "<th class=\"sgdg-tinymce-path\">" + localize.root_name + "</th>";
		html += "</tr>";
		html += "</thead>";
		html += "<tbody id=\"sgdg-tinymce-list\"></tbody>";
		html += "<tfoot>";
		html += "<tr>";
		html += "<td class=\"sgdg-tinymce-path\">" + localize.root_name + "</td>";
		html += "</tr>";
		html += "</tfoot>";
		html += "</table>";
		html += "<div class=\"sgdg-tinymce-footer\">";
		html += "<a id=\"sgdg-tinymce-insert\" class=\"button button-primary\">" + localize.insert_button + "</a>";
		html += "</div>";
		$("#sgdg-tinymce-modal").html(html);
		$("#sgdg-tinymce-insert").click(function() {tinymce_submit(editor);})
	}

	function tinymce_submit(editor)
	{
		if($( "#sgdg-tinymce-insert" ).attr( "disabled" ))
		{
			return;
		}
		editor.insertContent("[sgdg path=\"" + path.join("/") + "\"]");
		tb_remove();
	}

	function ajax_query()
	{
		$( "#sgdg-tinymce-list" ).html( "" );
		$( "#sgdg-tinymce-insert" ).attr( "disabled", "disabled" );
		$.get(localize.ajax_url, {
			_ajax_nonce: localize.nonce,
			action: "list_gallery_dir",
			path: path
			}, function(data)
			{
				$( "#sgdg-tinymce-insert" ).removeAttr( "disabled" );
				var html = "";
				if (path.length > 0) {
					html += "<tr><td class=\"row-title\"><label>..</label></td></tr>";
				}
				var len = data.length;
				for (var i = 0; i < len; i++) {
					html += "<tr class=\"";
					if ((path.length === 0 && i % 2 === 1) || (path.length > 0 && i % 2 === 0)) {
						html += "alternate";
					}
					html += "\"><td class=\"row-title\"><label>" + data[i] + "</label></td></tr>";
				}
				$( "#sgdg-tinymce-list" ).html( html );
				html = localize.root_name;
				len = path.length;
				for (i = 0; i < len; i++) {
					html += " > ";
					html += path[i];
				}
				$(".sgdg-tinymce-path").html( html );
				$("#sgdg-tinymce-list label").click(function()
					{
						var newDir = $( this ).html();
						if(newDir === "..")
						{
							path.pop();
						}
						else
						{
							path.push(newDir);
						}
						ajax_query();
					});
			}
		);
	}
});
