# --------------------------------------------------------------------------------
# Test MQS API
# --------------------------------------------------------------------------------

import glob, csv, string, re
from sys import argv, stdout

class trml:
	BLACK 	= '\033[30m'
	RED 	= '\033[31m'
	GREEN 	= '\033[32m'
	BOLD	= '\033[1m'
	NORMAL	= '\033[0;0m'
    
# --------------------------------------------------------------------------------
# Make API call

def make_api_call(method, uri, payload):
	#
	# Make a Call to the REST API at uri, using HTTP method, with payload
	#
			
	return res
	

# --------------------------------------------------------------------------------
# process_data function

def process_data(input_pattern, column_positions, output_prefix, joiner_function):
	
	cp = column_positions	
	total = 0
	for file in glob.glob(input_pattern):
		fileno = re.match(r'.*(\d+).txt', file)
		output = open(output_prefix + fileno.group(1) + '.tsv', 'wb')
		writer = csv.writer(output, delimiter='\t', quotechar='"', quoting=csv.QUOTE_ALL)
		tsv = open(file, 'rU')
		reader = csv.reader(tsv, delimiter='\t')
		rownum = 0
		for row in reader:
			extract_cols = range(len(row))
			content = list(row[i] for i in extract_cols)
			if rownum == 0:
				# Write header row
				writer.writerow(joiner_function(content))
			else:
				# Extract data from row
				title 		= content[cp['title']]
				firstname 	= content[cp['firstname']]
				lastname 	= content[cp['lastname']]
				company 	= content[cp['company']]

				cgn = make_greeting(title, firstname, lastname, company)
						
				print '{:20s}      {:40s}      {:40s}      {:40s}      {:40s}'.format(title, firstname, lastname, company, cgn)
				content[cp['result']] = cgn
				writer.writerow(joiner_function(content))
				total  += 1
			rownum += 1
		tsv.close()
		output.close()
	return total

# --------------------------------------------------------------------------------
# Check test results

def check_test_results(column_positions, output_prefix):
	stdout.write(trml.BOLD)
	print
	print '--------------------------------------------------------------------'
	print '{:4s}      {:20s}      {:20s}      {:20s}'.format('Line', 'Expected', 'Got', 'Test')
	print '--------------------------------------------------------------------'
	stdout.write(trml.NORMAL)
	cp = column_positions	
	passmsg = trml.GREEN + 'PASSED'
	failmsg = trml.RED   + 'FAILED'
	pcount = 0
	fcount = 0
	for file in glob.glob(output_prefix + '*.tsv'):
		tsv = open(file, 'rU')
		reader = csv.reader(tsv, delimiter='\t')
		rownum = 0
		for row in reader:
			extract_cols = range(len(row))
			content = list(row[i] for i in extract_cols)
			if rownum == 0:
				# Write header row
				extract_cols = range(len(row))
			else:
				# Extract data from row
				content = list(row[i] for i in extract_cols)
				expected = content[cp['expected']]
				greeting = content[cp['result']]
				passed = greeting == expected
				if not passed:
					 stdout.write(trml.BOLD)
				print '{:4d}      {:20s}      {:20s}      {:20s}'.format(rownum + 1, expected, greeting, passmsg if passed else failmsg), trml.BLACK
				if not passed:
					 stdout.write(trml.NORMAL)
				if passed:
					pcount += 1
				else:
					fcount += 1
			rownum += 1
		tsv.close()
	format = 'TEST RESULTS: ' + trml.GREEN + '%d PASSED ' + trml.RED + '%d FAILED'
	stdout.write(trml.BOLD)
	print '--------------------------------------------------------------------'
	print format % (pcount, fcount), trml.BLACK
	print '--------------------------------------------------------------------'
	print
	stdout.write(trml.NORMAL)

# --------------------------------------------------------------------------------
# RUN

if __name__ == '__main__':
	print
	print '----------------'
	print 'TESTING REST API'
	print '----------------'
	print
	in_pat  = 'tests/api_input*.txt'
	col_pos = {'title': 0, 'firstname': 1, 'lastname': 2, 'company': 3, 'expected': 4, 'result': 5}
	out_pre = 'tests/api_output'
	def joinfun(d) : return d 
	total = process_data(in_pat, col_pos, out_pre, joinfun)
	print
	print total, 'lines processed'
	print 'DONE'
	print
	if testmode:
		# Check the results of the test
		check_test_results(col_pos, out_pre)
