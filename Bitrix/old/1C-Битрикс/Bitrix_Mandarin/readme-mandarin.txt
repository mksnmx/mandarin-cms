��������� ������ ��������

� ������ ������ ��������� �� ������ zip:

1. �� ������� � �������� /bitrix/modules/ ������� ������� mandarinbank.mandarinbank
2. ����������� �����, �� �������� .last_version ����������� ��� ����� (install � lang) �� ������ � ������� /bitrix/modules/mandarinbank.mandarinbank - � ���� �������� ������ ��������� ��� ����� (install � lang)
3. � ����������������� �����, ������ Marketplace/������������� ������� (bitrix/admin/partner_modules.php) - ����� ������ mandarinbank.mandarinbank, �������� - ����������. ������ ����������.

��������� ������:

1. � ����������������� �����, ������ �������/���������/��������� ������� (bitrix/admin/sale_pay_system.php), ������ "�������� ��������� �������"
2. � ���� ���������� �������: ������ ����������������, �������� ������� mandarinbank
3. ������� ���������, �������� � ������� �� �������, ���� � ����� � �������, ��� ������ �����������
4. ���� �� ������� "�� ���������", ��������� ����:

����� � ������: ����� : ��������� ������
����� ������: ����� : ��� ������(ID)
Email ����������: ������������ : ����������� �����
��������� ���� : �������� : ��� ��������� ����
ID �������� : �������� : ��� merchant ID

���������.

������ ��� �������� �������:
callbackURL: http://{����}/payment/mandarinbank/st.php 
returnURL: http://{����}/payment/mandarinbank/state.php
