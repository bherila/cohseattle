<%@ Page Language="C#" AutoEventWireup="true" CodeBehind="Default.aspx.cs" Inherits="N2.Management.Default" %>

<%@ Register TagPrefix="edit" Namespace="N2.Edit.Web.UI.Controls" Assembly="N2.Management" %>
<% bool hasAdmin = System.IO.Directory.Exists(System.Web.Hosting.HostingEnvironment.MapPath("/N2/App/Js") ?? string.Empty); %>
<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!-->
<html class="no-js">
<!--<![endif]-->
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<title>N2 Management</title>
	<meta name="viewport" content="width=device-width">
<% if (hasAdmin) { %>
	<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
	<script type="text/javascript">window.jQuery || document.write('<script src="Resources/Js/jquery-1.9.1.min.js"><\/script>')</script>
	<script type="text/javascript" src="Resources/jquery-ui-1.10.2.custom/js/jquery-ui-1.10.2.custom.min.js"></script>
	<script type="text/javascript" src="Resources/angular-1.1.5/angular.min.js"></script>
	<script type="text/javascript" src="Resources/angular-1.1.5/angular-resource.min.js"></script>

	<link href="Resources/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
	<script src="Resources/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>

	<link rel="stylesheet" href="Resources/font-awesome/css/font-awesome.min.css">

	<script src="Resources/angular-ui-0.4.0/angular-ui.min.js" type="text/javascript"></script>
	<link href="Resources/angular-ui-0.4.0/angular-ui.min.css" rel="stylesheet" type="text/css" />

	<link href="Resources/bootstrap-components/bootstrap-datepicker.css" rel="stylesheet" type="text/css" />
	<script src="Resources/bootstrap-components/bootstrap-datepicker.js" type="text/javascript"></script>

	<link href="Resources/bootstrap-components/bootstrap-timepicker.css" rel="stylesheet" type="text/css" />
	<script src="Resources/bootstrap-components/bootstrap-timepicker.js" type="text/javascript"></script>

	<script src="Resources/bootstrap-components/angular-strap.min.js" type="text/javascript"></script>

	<script src="Resources/js/n2.js" type="text/javascript"></script>
	<link rel="stylesheet" type="text/css" href="Resources/css/n2.css" />
	<link rel="stylesheet" type="text/css" href="Resources/icons/flags.css" />

	<script type="text/javascript" src="<%= GetLocalizationPath() %>"></script>
	<script type="text/javascript" src="App/Js/Services.js"></script>
	<script type="text/javascript" src="App/Js/Controllers.js"></script>
	<script type="text/javascript" src="App/Js/Directives.js"></script>
<% } %>
<asp:PlaceHolder runat="server">
<% foreach(var module in N2.Context.Current.Container.ResolveAll<N2.Management.Api.ManagementModuleBase>()) { %>
	<!-- <%= module.GetType().Name %> -->
	<% foreach(var script in module.ScriptIncludes) { %>
	<script type="text/javascript" src="<%= N2.Web.Url.ResolveTokens(script) %>"></script>
	<% } %>
	<% foreach(var style in module.StyleIncludes) { %>
	<link type="text/css" href="<%= N2.Web.Url.ResolveTokens(style) %>" rel="stylesheet" />
	<% } %>
<% } %>
</asp:PlaceHolder>
</head>
<% if (hasAdmin) { %>
<body ng-controller="ManagementCtrl" ng-app="n2" x-context-menu-trigger=".item" ng-include src="Context.Partials.Management">
	<%--<div id="debug-context" class="debug" ng-bind-html-unsafe="Context | pretty"></div>--%>
</body>
<% } else { %>
<body>
	
	<h1>Management not available</h1>
	<p>Please install one of the following Nuget packages into your solution:</p>
	<ul>
		<li>N2CMS.Management</li>
		<li>N2CMS.Management.NoZip</li>
	</ul>
	<p>For example,</p>
	<pre>Install-Package N2CMS.Management.NoZip</pre>

</body>
<% } %>
</html>

<script runat="server">

	protected string GetLocalizationPath()
	{
		var culture = System.Threading.Thread.CurrentThread.CurrentUICulture;
		var languagePreferenceList = new[] { culture.ToString(), culture.TwoLetterISOLanguageName };
		foreach (var languageCode in languagePreferenceList)
		{
			var path = N2.Web.Url.ResolveTokens("{ManagementUrl}/App/i18n/" + languageCode + ".js.ashx");
			if (System.Web.Hosting.HostingEnvironment.VirtualPathProvider.FileExists(path))
				return path;
		}
		return "App/i18n/en.js.ashx";
	}
</script>
