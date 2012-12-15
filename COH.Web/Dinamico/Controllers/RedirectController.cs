using N2.Web;
using N2.Web.Mvc;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;

namespace Dinamico.Controllers
{
    [Controls(typeof(Models.Redirect))]
    public class LinkController : ContentController<Models.Redirect>
    {
        public override System.Web.Mvc.ActionResult Index()
        {
            return View(new string[] { CurrentItem.Link });
        }
    }
}