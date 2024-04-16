/*
** Zabbix
** Copyright (C) 2001-2024 Zabbix SIA
**
** This program is free software; you can redistribute it and/or modify
** it under the terms of the GNU General Public License as published by
** the Free Software Foundation; either version 2 of the License, or
** (at your option) any later version.
**
** This program is distributed in the hope that it will be useful,
** but WITHOUT ANY WARRANTY; without even the implied warranty of
** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
** GNU General Public License for more details.
**
** You should have received a copy of the GNU General Public License
** along with this program; if not, write to the Free Software
** Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
**/

#include "http.h"
#include "http_metrics.h"

static zbx_metric_t	parameters_common_http[] =
/*	KEY			FLAG		FUNCTION		TEST PARAMETERS */
{
	{"web.page.get",	CF_HAVEPARAMS,	web_page_get,		"localhost,,80"},
	{"web.page.perf",	CF_HAVEPARAMS,	web_page_perf,		"localhost,,80"},
	{"web.page.regexp",	CF_HAVEPARAMS,	web_page_regexp,	"localhost,,80,OK"},
	{0}
};

zbx_metric_t	*get_parameters_common_http(void)
{
	return &parameters_common_http[0];
}
