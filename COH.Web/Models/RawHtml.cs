using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using N2;
using N2.Web.Mvc;
using N2.Engine;
using N2.Details;

namespace MWMvc.Models
{
    [PartDefinition(Title = "Raw HTML", IconUrl = "~/N2/Resources/Icons/tag.png") ]
    public class RawHtml : ContentItem
    {
        [EditableText(Rows = 10, Columns = 50, TextMode = System.Web.UI.WebControls.TextBoxMode.MultiLine)]
        public string HTMLContent
        {
            get { return GetDetail("Body", ""); }
            set { SetDetail("Body", value, ""); }
        }
    }

    [Adapts(typeof(RawHtml))]
    public class RawHtmlAdapter : MvcAdapter
    {
        public override void RenderTemplate(System.Web.Mvc.HtmlHelper html, ContentItem model)
        {
            if (!(model is RawHtml))
                throw new ArgumentException("This adapter can only be used to adapt RawHTML parts");
            html.ViewContext.Writer.Write((model as RawHtml).HTMLContent);
        }
    }
}