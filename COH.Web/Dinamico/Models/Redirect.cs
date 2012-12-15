using N2;
using N2.Details;
using N2.Persistence;
using N2.Definitions;
using System.Web.UI.WebControls;

namespace Dinamico.Models
{
    [PageDefinition("Redirect", IconUrl = "~/N2/Resources/icons/link.png")]
    public class Redirect : PageModelBase, IStructuralPage
    {
        [EditableUrl("Link", 100, PersistAs = PropertyPersistenceLocation.Detail)]
        public virtual string Link
        {
            get { return GetDetail("Link", ""); }
            set { SetDetail("Link", value); }
        }
    }
}