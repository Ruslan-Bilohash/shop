# Copy to deploy.config.local.ps1 (gitignored) and fill in Hostinger SSH from hPanel → SSH Access.
@{
    Host     = '187.124.26.9'   # or srvXXX.hostinger.com from hPanel
    Port     = 22               # Hostinger often uses 65002 — check hPanel
    User     = 'u762384583'
    Password = ''               # leave empty if using SSH key
    RemoteRoot = '/home/u762384583/domains/bilohash.com/public_html/shop'
}