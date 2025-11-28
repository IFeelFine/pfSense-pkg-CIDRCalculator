# pfSense package Makefile for CIDRCalculator

PORTNAME= pfSense-pkg-CIDRCalculator
PORTVERSION= 0.1
CATEGORIES= net
MASTER_SITES= # empty
DISTFILES= # empty

MAINTAINER= 13614128+imdebating@users.noreply.github.com
COMMENT= Interactive CIDR Calculator for pfSense with IPv4/IPv6 support. Includes a dashboard widget.

NO_BUILD=yes
NO_MTREE=yes

do-extract:
	${MKDIR} ${WRKSRC}

do-install:
	${MKDIR} ${STAGEDIR}${PREFIX}/pkg
	${MKDIR} ${STAGEDIR}${PREFIX}/www
	${MKDIR} ${STAGEDIR}${PREFIX}/www/widgets
	${MKDIR} ${STAGEDIR}/etc/inc/priv
	${MKDIR} ${STAGEDIR}${DATADIR}
	${INSTALL_DATA} ${FILESDIR}${PREFIX}/pkg/cidr_calc.inc \
		${STAGEDIR}${PREFIX}/pkg
	${INSTALL_DATA} ${FILESDIR}${PREFIX}/pkg/cidr_calc.xml \
		${STAGEDIR}${PREFIX}/pkg
	${INSTALL_DATA} ${FILESDIR}${DATADIR}/info.xml \
		${STAGEDIR}${DATADIR}
	${INSTALL_DATA} ${FILESDIR}${PREFIX}/www/diagnostics_cidr_calculator.php \
		${STAGEDIR}${PREFIX}/www
	${INSTALL_DATA} ${FILESDIR}${PREFIX}/www/widgets/cidr_calculator.widget.php \
		${STAGEDIR}${PREFIX}/www/widgets
	${INSTALL_DATA} ${FILESDIR}/etc/inc/priv/cidr_calc.inc \
		${STAGEDIR}/etc/inc/priv

.include <bsd.port.mk>
