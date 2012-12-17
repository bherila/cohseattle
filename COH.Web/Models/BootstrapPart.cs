using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using N2.Details;
using N2;
using N2.Integrity;
using N2.Web.Mvc.Html;

namespace Dinamico.Models
{
    [PartDefinition]
    [WithEditableTemplateSelection(ContainerName = Defaults.Containers.Metadata)]
    [AvailableZone("Row Placeholder", "Content")]
    [RestrictChildren(typeof(BootstrapRow))]
    public class BootstrapContainer : PartModelBase
    {
        [EditableCheckBox(Name = "Fluid", CheckBoxText = "Is fluid?", Title = "Fixed/fluid")]
        public bool Fluid
        {
            get { return GetDetail<bool>("fluid", true); }
            set { SetDetail("fluid", value); }
        }
    }


    [PartDefinition]
    [WithEditableTemplateSelection(ContainerName = Defaults.Containers.Metadata)]
    [AvailableZone("Span Placeholder", "Content")]
    [RestrictChildren(typeof(BootstrapSpan))]
    //[RestrictParents(typeof(BootstrapContainer))]
    public class BootstrapRow : PartModelBase
    {

    }


    [PartDefinition]
    [WithEditableTemplateSelection(ContainerName = Defaults.Containers.Metadata)]
    [AvailableZone("Span Inner Content Placeholder", "Content")]
    public class BootstrapSpan : PartModelBase
    {

        [EditableNumber(Name ="Columns", SortOrder = 100, MaximumValue = "12", MinimumValue = "1") ]
        public virtual int Columns { 
            get { return GetDetail<int>("columns", 6); }
            set { SetDetail("columns", value); }
        }

        

    }


    public class BootstrapAdapter : N2.Web.Mvc.MvcAdapter
    {
        public bool DesignMode { get { return HttpContext.Current.Request["edit"] == "drag"; } }

        public override void RenderTemplate(System.Web.Mvc.HtmlHelper html, ContentItem model)
        {
            string @class = "";
            if (model is BootstrapContainer)
            {
                @class = "container";
                if ((model as BootstrapContainer).Fluid)
                    @class += "-fluid";
            }
            else if (model is BootstrapSpan)
            {
                @class = "span" + (model as BootstrapSpan).Columns.ToString();
            }
            else if (model is BootstrapRow)
            {
                @class = "row";
                if ((model.Parent as BootstrapContainer).Fluid)
                    @class += "-fluid";
            }

            if (DesignMode)
            {
                string title = model.GetContentType().Name;
                html.ViewContext.Writer.Write(String.Format(
    @"

<div style=""border: 1px solid #ccc; padding: 3px 3px 8px 3px; margin: 4px; width: 100%;"">
    <div style=""text-transform: uppercase; font-weight: bold; font-size: 8pt;"">{0}</div>
    <div>", title));
            }
            else
                html.ViewContext.Writer.Write(String.Format(@"<div class=""{0}"">", @class));
            
            html.DroppableZone(model, "Content").Render();
            
            if (DesignMode)
            {
                html.ViewContext.Writer.Write(@"

    </div>
    <div style=""clear: both;""></div>
</div>");
            }
            else
                html.ViewContext.Writer.Write("</div>");
        }
    }

    [N2.Engine.Adapts(typeof(BootstrapSpan))]
    public class BootstrapSpanAdapter : BootstrapAdapter { }

    [N2.Engine.Adapts(typeof(BootstrapRow))]
    public class BootstrapRowAdapter : BootstrapAdapter { }

    [N2.Engine.Adapts(typeof(BootstrapContainer))]
    public class BootstrapContainerAdapter : BootstrapAdapter { }

}