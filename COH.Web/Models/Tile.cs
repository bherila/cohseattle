using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using N2.Details;
using N2.Web.Mvc;
using N2;
using N2.Engine;
using Dinamico.Models;

namespace MWMvc.Models
{
    [PartDefinition(Title = "ContentTile", IconUrl = "~/N2/Resources/Icons/brick.png")]
    public class Tile : ContentPart
    {
        [EditableText]
        public string BackgroundColor
        {
            get { return GetDetail("BackgroundColor", "#006735"); }
            set { SetDetail("BackgroundColor", value, "#006735"); }
        }

        [EditableText]
        public string ForegroundColor
        {
            get { return GetDetail("ForegroundColor", "#fff"); }
            set { SetDetail("ForegroundColor", value, "#fff"); }
        }

        [EditableText(Rows = 10, Columns = 50, TextMode = System.Web.UI.WebControls.TextBoxMode.MultiLine)]
        public string HTMLContent
        {
            get { return GetDetail("Body", ""); }
            set { SetDetail("Body", value, ""); }
        }

        [EditableText]
        public string Padding
        {
            get { return GetDetail("Padding", "10px"); }
            set { SetDetail("Padding", value, ""); }
        }

        public override string TemplateKey
        {
            get { return "Tile"; }
            set { }
        }
    }

}