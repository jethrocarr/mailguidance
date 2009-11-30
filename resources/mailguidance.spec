Summary: MailGuidance Filter Control Application
Name: mailguidance
Version: 1.0.0
Release: 1.%{?dist}
License: AGPLv3
URL: http://www.amberdms.com/mailguidance
Group: Applications/Internet
Source0: mailguidance-%{version}.tar.bz2

BuildRoot: %{_tmppath}/%{name}-%{version}-%{release}-root-%(%{__id_u} -n)
BuildArch: noarch
BuildRequires: gettext
Requires: httpd, mod_ssl
Requires: php >= 5.1.6, mysql-server, php-mysql
Requires: php-pear, php-pear-Mail-Mime
Requires: perl, perl-DBD-MySQL
Prereq: httpd, php, mysql-server, php-mysql

%description
Mailguidance is a web-based application used to provide an easy interface to generate procmail filtering
rules with the purpose of filtering system administration emails.

%prep
%setup -q -n mailguidance-%{version}

%build


%install
rm -rf $RPM_BUILD_ROOT
mkdir -p -m0755 $RPM_BUILD_ROOT%{_sysconfdir}/mailguidance/
mkdir -p -m0755 $RPM_BUILD_ROOT%{_datadir}/mailguidance/

# install application files and resources
cp -pr * $RPM_BUILD_ROOT%{_datadir}/mailguidance/

# install configuration file
install -m0700 htdocs/include/sample-config.php $RPM_BUILD_ROOT%{_sysconfdir}/mailguidance/config.php
ln -s %{_sysconfdir}/mailguidance/config.php $RPM_BUILD_ROOT%{_datadir}/mailguidance/htdocs/include/config-settings.php

# install linking config file
install -m755 htdocs/include/config.php $RPM_BUILD_ROOT%{_datadir}/mailguidance/htdocs/include/config.php

# install the apache configuration file
mkdir -p $RPM_BUILD_ROOT%{_sysconfdir}/httpd/conf.d
install -m 644 resources/mailguidance-httpdconfig.conf $RPM_BUILD_ROOT%{_sysconfdir}/httpd/conf.d/mailguidance.conf


%post

# Reload apache
echo "Reloading httpd..."
/etc/init.d/httpd reload

# update/install the MySQL DB
if [ $1 == 1 ];
then
	# install - requires manual user MySQL setup
	echo "Run cd %{_datadir}/mailguidance/resources/; ./autoinstall.pl to install the SQL database."
else
	# upgrade - we can do it all automatically! :-)
	echo "Automatically upgrading the MySQL database..."
	%{_datadir}/mailguidance/resources/schema_update.pl --schema=%{_datadir}/mailguidance/sql/ -v
fi


%postun

# check if this is being removed for good, or just so that an
# upgrade can install.
if [ $1 == 0 ];
then
	# user needs to remove DB
	echo "Mailguidance has been removed, but the MySQL database and user will need to be removed manually."
fi


%clean
rm -rf $RPM_BUILD_ROOT

%files
%defattr(-,root,root)
%config %dir %{_sysconfdir}/mailguidance
%attr(770,root,apache) %config(noreplace) %{_sysconfdir}/mailguidance/config.php
%attr(660,root,apache) %config(noreplace) %{_sysconfdir}/httpd/conf.d/mailguidance.conf
%{_datadir}/mailguidance

%changelog
* Tue Dec 01 2009 Jethro Carr <jethro.carr@amberdms.com> 1.0.0
- Inital Application release

