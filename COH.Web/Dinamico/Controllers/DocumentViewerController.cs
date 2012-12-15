using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using System.Web.Mvc;
using N2.Web;
using N2.Web.Mvc;

namespace Dinamico.Controllers
{

    [Controls(typeof(Models.DocumentViewer))]
    public class DocumentViewerController : ContentController<Models.DocumentViewer>
    {

        public override ActionResult Index()
        {
            return View(CurrentItem.TemplateKey, CurrentItem);
        }

    }
}
