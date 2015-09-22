# Silverstripe GeneratePDF
## Introduction
This extension allow for a PDF version of any page to be created and served either via a direct link to the generated file or via a method that triggers the download of the generated pdf.

## Requirements
- Silverstripe 3.1
- [WKHTMLTOPDF library](http://wkhtmltopdf.org/)

## Installation

	composer require "dnadesign/silverstripe-generatepdf" "dev-master"

Extend any page type with the GeneratePDF extension as well as the page type controller with GeneratePDF_Controller extension. This can be done via the SilverStripe `YAML` config API

**mysite/_config/app.yml**

	Page:
	  extensions:
	    - GeneratePDF
	   
    Page_Controller:
	  extensions:
	    - GeneratePDF_Controller

**Define WKHTMLTOPDF path**
To use the library locally, you can define the path in the yaml file

	Page:
  		wkhtmltopdf_binary: /usr/local/bin/wkhtmltopdf


## Configuration
The `AutoGeneratePDF` extension refreshes the generated pdf upon publish. Everytime you republish a page with the extension GeneratePDF, the previous version of the pdf is deleted and a new one is recreated, keeping the generated file always up-to-date with the live version.

	Page:
	  extensions:
	    - AutoGeneratePDF

The `PublishRefreshPDF` allow for subpage to publish automatically the parent page in order to regenerate the pdf. It can also be used with silvertsripe-elemental to trigger the page publish when a widget (element) is published.

	SubPage:
	  extensions:
	    - PublishRefreshPDF
	    
	 BaseElement:
	  extensions:
	    - PublishRefreshPDF
