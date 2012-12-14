using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using System.Web.Mvc;
using System.Web.Routing;
using NHibernate;
using FluentNHibernate.Cfg;

namespace MWMvc
{
    // Note: For instructions on enabling IIS6 or IIS7 classic mode, 
    // visit http://go.microsoft.com/?LinkId=9394801

    public class MvcApplication : System.Web.HttpApplication
    {
        public static void RegisterGlobalFilters(GlobalFilterCollection filters)
        {
            filters.Add(new HandleErrorAttribute());
        }

        public static void RegisterRoutes(RouteCollection routes)
        {
            routes.IgnoreRoute("{resource}.axd/{*pathInfo}");

            routes.MapRoute(
                "Default", // Route name
                "{controller}/{action}/{id}", // URL with parameters
                new { controller = "Home", action = "Index", id = UrlParameter.Optional } // Parameter defaults
            );

        }

        private static ISessionFactory CreateSessionFactory()
        {
            return Fluently.Configure()
              .ExposeConfiguration(c => c.Properties.Add("hbm2ddl.keywords", "none"))
              .Database(
                FluentNHibernate.Cfg.Db.MySQLConfiguration.Standard.ConnectionString(c => c.FromConnectionStringWithKey("N2CMS"))
              )
              .Mappings(m =>
                  m.FluentMappings.AddFromAssemblyOf<MvcApplication>())
              .ExposeConfiguration(cfg => { new NHibernate.Tool.hbm2ddl.SchemaUpdate(cfg).Execute(false, true); })
              .BuildSessionFactory();
        }

        protected void Application_Start()
        {
            AreaRegistration.RegisterAllAreas();
            ViewEngines.Engines.Add(new RazorViewEngine());
            RegisterGlobalFilters(GlobalFilters.Filters);
            RegisterRoutes(RouteTable.Routes);


            // Setup Local NHibernate
            // Load from connection string: N2CMS
            AppSessionFactory = CreateSessionFactory();
        }


        public static ISessionFactory AppSessionFactory { get; protected set; }

    }
}