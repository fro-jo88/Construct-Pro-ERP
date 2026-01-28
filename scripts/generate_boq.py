import xlsxwriter
import sys
import os
import json

def create_boq(project_name, filename, boq_data=None):
    workbook = xlsxwriter.Workbook(filename)
    
    # Formats
    title_fmt = workbook.add_format({'bold': True, 'size': 14, 'align': 'center', 'valign': 'vcenter', 'border': 1})
    header_fmt = workbook.add_format({'bold': True, 'bg_color': '#D7E4BC', 'border': 1, 'align': 'center'})
    cell_fmt = workbook.add_format({'border': 1})
    num_fmt = workbook.add_format({'border': 1, 'num_format': '#,##0.00'})
    subtotal_fmt = workbook.add_format({'bold': True, 'border': 1, 'bg_color': '#EEECE1', 'num_format': '#,##0.00'})
    grand_total_fmt = workbook.add_format({'bold': True, 'border': 1, 'bg_color': '#D9D9D9', 'num_format': '#,##0.00', 'size': 12})
    
    # Defaults if no JSON
    if not boq_data:
        boq_data = {
            'sub': [
                {'no': '1.1', 'desc': 'Excavation & Earth Work', 'unit': 'm3', 'qty': 0, 'rate': 0},
                {'no': '1.2', 'desc' : 'Masonry Work', 'unit': 'm3', 'qty': 0, 'rate' : 0},
                {'no': '1.3', 'desc': 'Concrete Work', 'unit': 'm3', 'qty': 0, 'rate': 0}
            ],
            'super': [
                {'no': '2.1', 'desc': 'Concrete Work', 'unit': 'm3', 'qty': 0, 'rate': 0},
                {'no': '2.2', 'desc': 'Block Work', 'unit': 'm2', 'qty': 0, 'rate': 0},
                {'no': '2.3', 'desc': 'Carpentry', 'unit': 'pcs', 'qty': 0, 'rate': 0}
            ]
        }

    # --- SHEET 3: DETAILED BOQ ---
    ws3 = workbook.add_worksheet('Detailed BOQ')
    ws3.set_column('B:B', 60)
    ws3.set_column('C:F', 15)
    ws3.merge_range('A1:F1', f'DETAILED BILL OF QUANTITIES FOR {project_name.upper()}', title_fmt)
    
    headers = ['Item No', 'Description', 'Unit', 'Quantity', 'Rate', 'Amount']
    for col, head in enumerate(headers):
        ws3.write(2, col, head, header_fmt)

    row = 3
    # Sub Structure
    ws3.write(row, 0, 'A', workbook.add_format({'bold': True}))
    ws3.write(row, 1, 'SUB STRUCTURE', workbook.add_format({'bold': True}))
    row += 1
    
    start_row_a = row + 1
    for item in boq_data['sub']:
        ws3.write(row, 0, item['no'], cell_fmt)
        ws3.write(row, 1, item['desc'], cell_fmt)
        ws3.write(row, 2, item['unit'], cell_fmt)
        ws3.write(row, 3, item['qty'], cell_fmt)
        ws3.write(row, 4, item['rate'], num_fmt)
        ws3.write_formula(row, 5, f'=D{row+1}*E{row+1}', num_fmt)
        row += 1
    end_row_a = row
    ws3.write(row, 1, 'TOTAL CARRIED TO SUMMARY (SUB STRUCTURE)', subtotal_fmt)
    ws3.write_formula(row, 5, f'=SUM(F{start_row_a}:F{end_row_a})', subtotal_fmt)
    total_a_ref = f"'Detailed BOQ'!F{row+1}"
    row += 2

    # Super Structure
    ws3.write(row, 0, 'B', workbook.add_format({'bold': True}))
    ws3.write(row, 1, 'SUPER STRUCTURE', workbook.add_format({'bold': True}))
    row += 1
    
    start_row_b = row + 1
    for item in boq_data['super']:
        ws3.write(row, 0, item.get('no', ''), cell_fmt)
        ws3.write(row, 1, item.get('desc', ''), cell_fmt)
        ws3.write(row, 2, item.get('unit', ''), cell_fmt)
        ws3.write(row, 3, item.get('qty', 0), cell_fmt)
        ws3.write(row, 4, item.get('rate', 0), num_fmt)
        ws3.write_formula(row, 5, f'=D{row+1}*E{row+1}', num_fmt)
        row += 1
    end_row_b = row
    ws3.write(row, 1, 'TOTAL CARRIED TO SUMMARY (SUPER STRUCTURE)', subtotal_fmt)
    ws3.write_formula(row, 5, f'=SUM(F{start_row_b}:F{end_row_b})', subtotal_fmt)
    total_b_ref = f"'Detailed BOQ'!F{row+1}"

    # --- SHEET 2: BOQ SUMMARY ---
    ws2 = workbook.add_worksheet('BOQ Summary')
    ws2.set_column('B:B', 50); ws2.set_column('C:F', 15)
    ws2.merge_range('A1:F1', f'SUMMARY OF BILL OF QUANTITIES FOR {project_name.upper()}', title_fmt)
    for col, head in enumerate(headers): ws2.write(2, col, head, header_fmt)
    ws2.write(3, 0, 'A', cell_fmt); ws2.write(3, 1, 'SUB STRUCTURE', cell_fmt); ws2.write_formula(3, 5, total_a_ref, num_fmt)
    ws2.write(4, 0, 'B', cell_fmt); ws2.write(4, 1, 'SUPER STRUCTURE', cell_fmt); ws2.write_formula(4, 5, total_b_ref, num_fmt)
    ws2.write(6, 4, 'Total (A+B)', workbook.add_format({'bold': True}))
    ws2.write_formula(6, 5, '=F4+F5', subtotal_fmt)
    ws2.write(7, 4, 'VAT (15%)', workbook.add_format({'bold': True}))
    ws2.write_formula(7, 5, '=F7*0.15', subtotal_fmt)
    ws2.write(8, 4, 'TOTAL WITH VAT', workbook.add_format({'bold': True, 'bg_color': '#FFFF00'}))
    ws2.write_formula(8, 5, '=F7+F8', grand_total_fmt)
    
    # --- SHEET 1: GRAND SUMMARY ---
    ws1 = workbook.add_worksheet('Grand Summary')
    ws1.set_column('B:B', 60); ws1.set_column('D:D', 20)
    ws1.merge_range('A1:D1', f'GRAND SUMMARY FOR {project_name.upper()} PROJECT', title_fmt)
    for col, head in enumerate(['Item No', 'Description', 'Unit', 'Amount (Birr)']): ws1.write(2, col, head, header_fmt)
    ws1.write(3, 0, '1', cell_fmt); ws1.write(3, 1, f'{project_name} (Build Project)', cell_fmt)
    ws1.write(3, 2, 'LS', cell_fmt); ws1.write_formula(3, 3, "'BOQ Summary'!F7", num_fmt)
    ws1.write(5, 1, 'Total without VAT', workbook.add_format({'bold': True}))
    ws1.write_formula(5, 3, "'BOQ Summary'!F7", num_fmt)
    ws1.write(6, 1, 'VAT (15%)', workbook.add_format({'bold': True}))
    ws1.write_formula(6, 3, "'BOQ Summary'!F8", num_fmt)
    ws1.write(7, 1, 'TOTAL WITH VAT (15%)', workbook.add_format({'bold': True, 'bg_color': '#D9E1F2'}))
    ws1.write_formula(7, 3, "'BOQ Summary'!F9", grand_total_fmt)

    workbook.close()

if __name__ == "__main__":
    if len(sys.argv) < 3:
        print("Usage: python generate_boq.py <project_name> <output_file> [json_data]")
        sys.exit(1)
    
    proj_name = sys.argv[1]
    out_file = sys.argv[2]
    data = None
    if len(sys.argv) > 3:
        try:
            data = json.loads(sys.argv[3])
        except:
            pass
            
    create_boq(proj_name, out_file, data)
    print(f"Created: {out_file}")
