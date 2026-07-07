# Copy to deploy.config.local.ps1 (gitignored) and fill in Hostinger SSH from hPanel → SSH Access.
$DeployHost = '45.84.204.61'   # SSH host from hPanel → SSH Access
$Port       = 65002             # Hostinger shared hosting SSH port
$User       = 'u762384583'
$Password   = ''               # leave empty if using SSH key
$RemoteRoot = '/home/u762384583/domains/bilohash.com/public_html/shop'