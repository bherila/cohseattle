using N2;
using N2.Details;
using N2.Persistence;
using N2.Definitions;
using System.Web.UI.WebControls;

namespace Dinamico.Models
{
    public enum DocumentViewerMode { Display, Download }

    [PageDefinition("Document Viewer", IconUrl = "~/N2/Resources/icons/page_white_acrobat.png")]
    public class DocumentViewer : PageModelBase, IStructuralPage
    {
        [EditableFileUpload("Attachment", 100, PersistAs = PropertyPersistenceLocation.Detail)]
        public virtual string Attachment
        {
            get { return GetDetail("Attachment", ""); }
            set { SetDetail("Attachment", value); }
        }

        [EditableEnum(typeof(DocumentViewerMode), PersistAs = PropertyPersistenceLocation.Detail)]
        public virtual DocumentViewerMode Mode
        {
            get { return GetDetail("Mode", DocumentViewerMode.Display); }
            set { SetDetail("Mode", value); }
        }


    }
}