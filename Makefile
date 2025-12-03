# pfSense package Makefile for CIDRCalculator

PORTNAME=					pfSense-pkg-CIDRCalculator
PORTVERSION=			0.1.0
PORTREVISION=			1
CATEGORIES=				net
MASTER_SITES=			# empty
DISTFILES=				# empty

MAINTAINER=				13614128+imdebating@users.noreply.github.com
COMMENT=					Interactive CIDR Calculator for pfSense with IPv4/IPv6 support. Includes a dashboard widget.

LICENSE=					APACHE20

NO_BUILD=					yes
NO_MTREE=					yes
NO_ARCH=					yes

SUB_FILES=				pkg-install pkg-deinstall
SUB_LIST=					PORTNAME=${PORTNAME}

DATADIR=					${PREFIX}/share/${PORTNAME}

do-extract:
	${MKDIR} ${WRKSRC}

do-install:
	${MKDIR} ${STAGEDIR}${PREFIX}/pkg
	${MKDIR} ${STAGEDIR}${PREFIX}/www
	${MKDIR} ${STAGEDIR}${PREFIX}/www/widgets/widgets
	${MKDIR} ${STAGEDIR}/etc/inc/priv
	${MKDIR} ${STAGEDIR}${DATADIR}
	${INSTALL_DATA} ${FILESDIR}${PREFIX}/pkg/cidr_calc.inc \
		${STAGEDIR}${PREFIX}/pkg
	${INSTALL_DATA} ${FILESDIR}${PREFIX}/pkg/cidr_calc.xml \
		${STAGEDIR}${PREFIX}/pkg
	${INSTALL_DATA} ${FILESDIR}${DATADIR}/info.xml \
		${STAGEDIR}${DATADIR}
	${INSTALL_DATA} ${FILESDIR}${PREFIX}/www/diag_cidr_calculator.php \
		${STAGEDIR}${PREFIX}/www
	${INSTALL_DATA} ${FILESDIR}${PREFIX}/www/widgets/widgets/scidr_calculator.widget.php \
		${STAGEDIR}${PREFIX}/www/widgets/widgets
	${INSTALL_DATA} ${FILESDIR}/etc/inc/priv/cidr_calc.priv.inc \
		${STAGEDIR}/etc/inc/priv

	@${REINPLACE_CMD} -i '' -e "s|%%PKGVERSION%%|${PKGVERSION}|" \
		${STAGEDIR}${DATADIR}/info.xml

.include <bsd.port.mk>
